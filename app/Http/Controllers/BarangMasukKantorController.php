<?php
namespace App\Http\Controllers;
use App\Models\BarangMasukKantor;
use App\Models\ProsesFinishing;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangMasukKantorController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');
        $page    = (int) request('page', 1);
        $perPage = 15;

        // Step 1: Paginate group keys (no_surat_jalan) di database
        $groupQuery = BarangMasukKantor::select('no_surat_jalan', \DB::raw('MAX(tanggal_kirim) as max_tanggal'))
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('no_surat_jalan', 'like', "%{$search}%")
            ))
            ->groupBy('no_surat_jalan')
            ->orderByDesc('max_tanggal');

        $total = $groupQuery->get()->count();
        $groupKeys = $groupQuery->skip(($page - 1) * $perPage)->take($perPage)->pluck('no_surat_jalan');

        // Step 2: Ambil rows hanya untuk group keys halaman ini
        $pageRows = BarangMasukKantor::whereIn('no_surat_jalan', $groupKeys)->latest()->get();

        $grouped = $pageRows->groupBy('no_surat_jalan')->map(function ($rows, $noSuratJalan) {
            $models = $rows->map(fn($r) => [
                'id'              => $r->id,
                'model'           => $r->model,
                'pcs_barang_jadi' => $r->pcs_barang_jadi,
            ])->values();
            return [
                'no_surat_jalan'  => $noSuratJalan,
                'tanggal_kirim'   => $rows->first()->tanggal_kirim,
                'jumlah_model'    => $rows->count(),
                'total_pcs'       => $rows->sum(fn($r) => (int)($r->pcs_barang_jadi ?? 0)),
                'models'          => $models,
            ];
        })->sortByDesc('tanggal_kirim')->values();

        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Hitung stok kantor dari aggregation (ringan karena sudah GROUP BY)
        $kantorSums = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->pluck('total', 'model');

        // Stok yang tersedia untuk masuk kantor = finishing - yang sudah masuk kantor
        $modelOptions = ProsesFinishing::selectRaw("model, SUM(pcs_barang_jadi) as max_pcs, MAX(harga_satuan) as harga_satuan")
            ->whereNotNull('pcs_barang_jadi')->where('pcs_barang_jadi', '>', 0)
            ->whereNotNull('tanggal_selesai')
            ->groupBy('model')->orderBy('model')->get()
            ->map(function ($r) use ($kantorSums) {
                $r->max_pcs = (int) $r->max_pcs - ($kantorSums[$r->model] ?? 0);
                return $r;
            })
            ->filter(fn($r) => $r->max_pcs > 0)->values();

        return Inertia::render('BarangMasukKantor/Index', [
            'data'           => $data,
            'modelOptions'   => $modelOptions,
            'nextSuratJalan' => $this->nextSuratJalan(BarangMasukKantor::class, 'SJ-KANTOR-'),
        ]);
    }

    public function create() { return Inertia::render('BarangMasukKantor/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_jalan'           => 'nullable|string|max:100',
            'tanggal_kirim'            => 'required|date',
            'models'                   => 'required|array|min:1',
            'models.*.model'           => 'required|string|max:200',
            'models.*.pcs_barang_jadi' => 'required|integer|min:1',
        ]);
        
        // Generate nomor surat jalan jika kosong
        $noSuratJalan = $request->no_surat_jalan ?: $this->nextSuratJalan(BarangMasukKantor::class, 'SJ-KANTOR-');
        
        $now  = now();
        $rows = collect($request->models)->map(function($m) use ($now, $noSuratJalan, $request) {
            // Ambil harga dari proses_finishing berdasarkan model (ambil yang terakhir)
            $finishing = \App\Models\ProsesFinishing::where('model', $m['model'])
                ->orderBy('id', 'desc')
                ->first();
            
            return [
                'no_surat_jalan'  => $noSuratJalan,
                'tanggal_kirim'   => $request->tanggal_kirim,
                'po'              => $finishing->po ?? null,
                'model'           => $m['model'],
                'pcs_barang_jadi' => $m['pcs_barang_jadi'],
                'harga_satuan'    => $finishing->harga_satuan ?? 0,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        })->toArray();
        BarangMasukKantor::insert($rows);
        return redirect()->route('barang-masuk-kantor.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BarangMasukKantor $barangMasukKantor) { return Inertia::render('BarangMasukKantor/Form', ['item' => $barangMasukKantor]); }

    public function update(Request $request, BarangMasukKantor $barangMasukKantor)
    {
        $request->validate(['pcs_barang_jadi' => 'required|integer|min:1']);
        $barangMasukKantor->update($request->only(['pcs_barang_jadi']));
        return redirect()->route('barang-masuk-kantor.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(BarangMasukKantor $barangMasukKantor)
    {
        $barangMasukKantor->delete();
        return redirect()->route('barang-masuk-kantor.index')->with('message', 'Data berhasil dihapus.');
    }
}
