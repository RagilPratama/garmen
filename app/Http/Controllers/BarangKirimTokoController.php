<?php
namespace App\Http\Controllers;
use App\Models\BarangKirimToko;
use App\Models\BarangMasukKantor;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangKirimTokoController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');
        $allRows = BarangKirimToko::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            return [
                'po'             => $po,
                'tanggal_kirim'  => $rows->min('tanggal_kirim'),
                'no_surat_jalan' => $rows->first()->no_surat_jalan,
                'jumlah_model'   => $rows->count(),
                'total_pcs'      => $rows->sum(fn($r) => (int)($r->pcs_barang_jadi ?? 0)),
                'models'         => $rows->map(fn($r) => [
                    'id'             => $r->id,
                    'model'          => $r->model,
                    'pcs_barang_jadi' => $r->pcs_barang_jadi,
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

        // Derive sums from already-loaded $allRows — no extra DB query needed
        $tokoSums = $allRows->groupBy(fn($r) => $r->po . '|||' . $r->model)
            ->map(fn($rows) => $rows->sum(fn($r) => (int) $r->pcs_barang_jadi));

        $poOptions = BarangMasukKantor::selectRaw("po, model, SUM(pcs_barang_jadi) as max_pcs")
            ->where('pcs_barang_jadi', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->map(function ($r) use ($tokoSums) {
                $key        = $r->po . '|||' . $r->model;
                $r->max_pcs = (int) $r->max_pcs - ($tokoSums[$key] ?? 0);
                return $r;
            })
            ->filter(fn($r) => $r->max_pcs > 0)->values();

        return Inertia::render('BarangKirimToko/Index', [
            'data'           => $data,
            'poOptions'      => $poOptions,
            'nextSuratJalan' => $this->nextSuratJalan(BarangKirimToko::class, 'SJ-TOKO-'),
        ]);
    }

    public function create() { return Inertia::render('BarangKirimToko/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_jalan'           => 'nullable|string|max:100',
            'tanggal_kirim'            => 'required|date',
            'models'                   => 'required|array|min:1',
            'models.*.po'              => 'required|string|max:100',
            'models.*.model'           => 'required|string|max:200',
            'models.*.pcs_barang_jadi' => 'required|integer|min:1',
        ]);
        $now  = now();
        $rows = collect($request->models)->map(fn($m) => [
            'no_surat_jalan'  => $request->no_surat_jalan,
            'tanggal_kirim'   => $request->tanggal_kirim,
            'po'              => $m['po'],
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
