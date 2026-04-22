<?php

namespace App\Http\Controllers;

use App\Models\JualGudang;
use App\Models\BarangMasukKantor;
use App\Models\PenjualanPembayaran;
use App\Models\Rekening;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JualGudangController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search  = request('search');
        $allRows = JualGudang::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('buyer', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('no_nota', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
            ))->get();

        // Load pembayaran per no_nota
        $pembayaranMap = PenjualanPembayaran::where('channel', 'gudang')
            ->get()
            ->groupBy('no_nota');

        $grouped = $allRows->groupBy('no_nota')->map(function ($rows, $nota) use ($pembayaranMap) {
            $models = $rows->map(fn($r) => [
                'id'           => $r->id,
                'model'        => $r->model,
                'pcs'          => $r->pcs,
                'harga_satuan' => $r->harga_satuan,
                'diskon'       => $r->diskon,
                'total_harga'  => $r->total_harga,
                'status'       => $r->status,
            ])->values();
            $totalHarga   = $rows->sum(fn($r) => (float) $r->total_harga);
            $pembayarans  = $pembayaranMap->get($nota, collect())->values();
            $totalBayar   = $pembayarans->sum('jumlah');
            return [
                'no_nota'      => $nota,
                'tanggal_nota' => $rows->min('tanggal_nota'),
                'buyer'        => $rows->first()->buyer,
                'status'       => $rows->first()->status,
                'jumlah_model' => $rows->count(),
                'total_pcs'    => $rows->sum(fn($r) => (int) $r->pcs),
                'total_harga'  => $totalHarga,
                'total_bayar'  => $totalBayar,
                'sisa_piutang' => max(0, $totalHarga - $totalBayar),
                'models'       => $models,
                'pembayarans'  => $pembayarans,
            ];
        })->sortByDesc('tanggal_nota')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Stok gudang = total masuk kantor - sudah terjual (lunas + pending) - sudah kirim ke toko, per model
        $masuk = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total, MAX(id) as last_id')
            ->groupBy('model')->get()->keyBy('model');
        $terjual = JualGudang::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'piutang'])
            ->groupBy('model')->get()->keyBy('model');
        $kirimToko = \App\Models\BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');

        $stokOptions = $masuk->map(function ($row) use ($terjual, $kirimToko) {
            $sisa = (int) $row->total 
                - (int) ($terjual->get($row->model)?->total ?? 0)
                - (int) ($kirimToko->get($row->model)?->total ?? 0);
            // Ambil harga dari record terakhir barang masuk kantor
            $lastRecord = BarangMasukKantor::find($row->last_id);
            $harga = (float) ($lastRecord->harga_satuan ?? 0);

            // Fallback ke proses_finishing jika harga masih 0 (data lama)
            if ($harga <= 0) {
                $harga = (float) (\App\Models\ProsesFinishing::where('model', $row->model)
                    ->where('harga_satuan', '>', 0)
                    ->orderBy('id', 'desc')
                    ->value('harga_satuan') ?? 0);
            }

            return [
                'model'        => $row->model,
                'sisa_stok'    => $sisa,
                'harga_satuan' => $harga,
            ];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('JualGudang/Index', [
            'data'           => $data,
            'stokOptions'    => $stokOptions,
            'nextNota'       => $this->nextCode(JualGudang::class, 'no_nota', 'NT-GDG-'),
            'rekeningOptions' => Rekening::orderBy('bank')->get(['id', 'bank', 'nama', 'nomor_rekening']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_nota'               => 'nullable|string|max:100',
            'tanggal_nota'          => 'required|date',
            'buyer'                 => 'required|string|max:200',
            'status'                => 'required|string|in:piutang,lunas,batal',
            'discount'              => 'nullable|numeric|min:0|max:100',
            'models'                => 'required|array|min:1',
            'models.*.model'        => 'required|string|max:200',
            'models.*.pcs'          => 'required|integer|min:1',
            'models.*.harga_satuan' => 'required|numeric|min:0',
            // Payment fields
            'bayar'                 => 'nullable|numeric|min:0',
            'metode'                => 'nullable|string|in:cash,transfer,debit',
            'rekening_id'           => 'nullable|exists:rekening,id',
            'keterangan_pembayaran' => 'nullable|string|max:255',
        ]);
        $now  = now();
        $diskonPersen = $request->discount ?? 0;
        $rows = collect($request->models)->map(fn($m) => [
            'no_nota'      => $request->no_nota,
            'tanggal_nota' => $request->tanggal_nota,
            'buyer'        => $request->buyer,
            'status'       => $request->status,
            'model'        => $m['model'],
            'pcs'          => $m['pcs'],
            'harga_satuan' => $m['harga_satuan'],
            'diskon'       => $diskonPersen,
            'total_harga'  => ($m['pcs'] * $m['harga_satuan']) * (1 - $diskonPersen / 100),
            'bayar'        => $request->bayar ?? 0,
            'po'           => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();
        JualGudang::insert($rows);

        // Record payment in PenjualanPembayaran
        if ($request->bayar > 0) {
            PenjualanPembayaran::create([
                'no_nota'       => $request->no_nota,
                'channel'       => 'gudang',
                'tanggal_bayar' => $request->tanggal_nota,
                'jumlah'        => $request->bayar,
                'metode'        => $request->metode ?? 'cash',
                'rekening_id'   => $request->rekening_id,
                'keterangan'    => $request->keterangan_pembayaran ?? 'Pembayaran awal saat buat nota',
            ]);
        }

        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, JualGudang $jualGudang)
    {
        $validated = $request->validate([
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);
        $validated['total_harga'] = ($validated['pcs'] * $validated['harga_satuan']) * (1 - $jualGudang->diskon / 100);
        $jualGudang->update($validated);
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function updateNotaStatus(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string|max:100',
            'status'  => 'required|string|in:piutang,lunas,batal',
        ]);
        JualGudang::where('no_nota', $request->no_nota)->update(['status' => $request->status]);
        return redirect()->route('jual-gudang.index')->with('message', 'Status nota berhasil diperbarui.');
    }

    public function destroy(JualGudang $jualGudang)
    {
        $jualGudang->delete();
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil dihapus.');
    }
}
