<?php
namespace App\Http\Controllers;
use App\Models\BarangKirimToko;
use App\Models\BarangMasukKantor;
use App\Models\ProsesFinishing;
use App\Models\Toko;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangKirimTokoController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');
        $allRows = BarangKirimToko::with('toko')
            ->latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('model', 'like', "%{$search}%")
                ->orWhere('no_surat_jalan', 'like', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('no_surat_jalan')->map(function ($rows, $sj) {
            return [
                'no_surat_jalan' => $sj,
                'tanggal_kirim'  => $rows->min('tanggal_kirim'),
                'toko'           => $rows->first()->toko?->nama_toko ?? 'Kantor',
                'jumlah_model'   => $rows->count(),
                'total_pcs'      => $rows->sum(fn($r) => (int)($r->pcs_barang_jadi ?? 0)),
                'models'         => $rows->map(fn($r) => [
                    'id'             => $r->id,
                    'model'          => $r->model,
                    'pcs_barang_jadi' => $r->pcs_barang_jadi,
                    'toko'           => $r->toko?->nama_toko ?? 'Kantor',
                ])->values(),
            ];
        })->sortByDesc('tanggal_kirim')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get stok barang siap kirim ke toko (dari stok kantor, bukan dari finishing)
        // Stok kantor = barang masuk kantor - jual gudang - kirim toko
        $masukKantor = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total_masuk')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        $jualGudang = \App\Models\JualGudang::selectRaw('model, SUM(pcs) as total_jual')
            ->whereIn('status', ['lunas', 'piutang'])
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        $kirimToko = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total_kirim')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        $stokOptions = $masukKantor->map(function ($row) use ($jualGudang, $kirimToko) {
            $totalMasuk = (int) $row->total_masuk;
            $totalJual  = (int) ($jualGudang->get($row->model)?->total_jual ?? 0);
            $totalKirim = (int) ($kirimToko->get($row->model)?->total_kirim ?? 0);
            
            // Stok kantor yang tersedia = masuk kantor - jual gudang - kirim toko
            $stokKantor = $totalMasuk - $totalJual - $totalKirim;

            return [
                'model'     => $row->model,
                'max_pcs'   => max(0, $stokKantor),
            ];
        })
        ->filter(fn($r) => $r['max_pcs'] > 0)
        ->values();

        // Get list toko untuk dropdown
        $tokos = \App\Models\Toko::where('is_active', true)->get(['id', 'nama_toko', 'kode_toko']);

        return Inertia::render('BarangKirimToko/Index', [
            'data'           => $data,
            'stokOptions'    => $stokOptions,
            'tokos'          => $tokos,
            'nextSuratJalan' => $this->nextSuratJalan(BarangKirimToko::class, 'SJ-TOKO-'),
        ]);
    }

    public function create() { return Inertia::render('BarangKirimToko/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'toko_id'                  => 'nullable|exists:tokos,id',
            'no_surat_jalan'           => 'nullable|string|max:100',
            'tanggal_kirim'            => 'required|date',
            'models'                   => 'required|array|min:1',
            'models.*.model'           => 'required|string|max:200',
            'models.*.pcs_barang_jadi' => 'required|integer|min:1',
        ]);
        $now  = now();
        $rows = collect($request->models)->map(fn($m) => [
            'toko_id'         => $request->toko_id,
            'no_surat_jalan'  => $request->no_surat_jalan,
            'tanggal_kirim'   => $request->tanggal_kirim,
            'po'              => null, // Tidak lagi terikat PO
            'model'           => $m['model'],
            'pcs_barang_jadi' => $m['pcs_barang_jadi'],
            'created_at'      => $now,
            'updated_at'      => $now,
        ])->toArray();
        BarangKirimToko::insert($rows);
        return redirect()->route('barang-kirim-toko.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BarangKirimToko $barangKirimToko) { return Inertia::render('BarangKirimToko/Form', ['item' => $barangKirimToko]); }

    public function update(Request $request, BarangKirimToko $barangKirimToko)
    {
        $request->validate(['pcs_barang_jadi' => 'required|integer|min:1']);
        $barangKirimToko->update($request->only(['pcs_barang_jadi']));
        return redirect()->route('barang-kirim-toko.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(BarangKirimToko $barangKirimToko)
    {
        $barangKirimToko->delete();
        return redirect()->route('barang-kirim-toko.index')->with('message', 'Data berhasil dihapus.');
    }
}
