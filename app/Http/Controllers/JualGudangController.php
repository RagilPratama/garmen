<?php

namespace App\Http\Controllers;

use App\Models\JualGudang;
use App\Models\BarangMasukKantor;
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
                ->where('buyer', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_nota', 'ilike', "%{$search}%")
                ->orWhere('status', 'ilike', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('no_nota')->map(function ($rows, $nota) {
            $models = $rows->map(fn($r) => [
                'id'           => $r->id,
                'model'        => $r->model,
                'pcs'          => $r->pcs,
                'harga_satuan' => $r->harga_satuan,
                'total_harga'  => $r->total_harga,
                'status'       => $r->status,
            ])->values();
            return [
                'no_nota'      => $nota,
                'tanggal_nota' => $rows->min('tanggal_nota'),
                'buyer'        => $rows->first()->buyer,
                'status'       => $rows->first()->status,
                'jumlah_model' => $rows->count(),
                'total_pcs'    => $rows->sum(fn($r) => (int) $r->pcs),
                'total_harga'  => $rows->sum(fn($r) => (float) $r->total_harga),
                'models'       => $models,
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

        // Stok gudang = total masuk kantor - sudah terjual (lunas + pending), per model
        $masuk = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total, MAX(id) as last_id')
            ->groupBy('model')->get()->keyBy('model');
        $terjual = JualGudang::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        $stokOptions = $masuk->map(function ($row) use ($terjual) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            // Ambil harga dari record terakhir barang masuk kantor
            $lastRecord = BarangMasukKantor::find($row->last_id);
            return [
                'model' => $row->model, 
                'sisa_stok' => $sisa,
                'harga_satuan' => $lastRecord->harga_satuan ?? 0
            ];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('JualGudang/Index', [
            'data'        => $data,
            'stokOptions' => $stokOptions,
            'nextNota'    => $this->nextCode(JualGudang::class, 'no_nota', 'NT-GDG-'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_nota'               => 'nullable|string|max:100',
            'tanggal_nota'          => 'required|date',
            'buyer'                 => 'required|string|max:200',
            'status'                => 'required|string|in:pending,lunas,batal',
            'models'                => 'required|array|min:1',
            'models.*.model'        => 'required|string|max:200',
            'models.*.pcs'          => 'required|integer|min:1',
            'models.*.harga_satuan' => 'required|numeric|min:0',
        ]);
        $now  = now();
        $rows = collect($request->models)->map(fn($m) => [
            'no_nota'      => $request->no_nota,
            'tanggal_nota' => $request->tanggal_nota,
            'buyer'        => $request->buyer,
            'status'       => $request->status,
            'model'        => $m['model'],
            'pcs'          => $m['pcs'],
            'harga_satuan' => $m['harga_satuan'],
            'total_harga'  => $m['pcs'] * $m['harga_satuan'],
            'po'           => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();
        JualGudang::insert($rows);
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, JualGudang $jualGudang)
    {
        $validated = $request->validate([
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        $jualGudang->update($validated);
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function updateNotaStatus(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string|max:100',
            'status'  => 'required|string|in:pending,lunas,batal',
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
