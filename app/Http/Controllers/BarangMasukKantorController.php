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
        $allRows = BarangMasukKantor::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            $models = $rows->map(fn($r) => [
                'id'              => $r->id,
                'model'           => $r->model,
                'pcs_barang_jadi' => $r->pcs_barang_jadi,
            ])->values();
            return [
                'po'              => $po,
                'tanggal_kirim'   => $rows->min('tanggal_kirim'),
                'no_surat_jalan'  => $rows->first()->no_surat_jalan,
                'jumlah_model'    => $rows->count(),
                'total_pcs'       => $rows->sum(fn($r) => (int)($r->pcs_barang_jadi ?? 0)),
                'models'          => $models,
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

        $alreadyKantor = BarangMasukKantor::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesFinishing::selectRaw("po, model, SUM(pcs_barang_jadi) as max_pcs")
            ->whereNotNull('pcs_barang_jadi')->where('pcs_barang_jadi', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyKantor))->values();

        return Inertia::render('BarangMasukKantor/Index', [
            'data'           => $data,
            'poOptions'      => $poOptions,
            'nextSuratJalan' => $this->nextSuratJalan(BarangMasukKantor::class, 'SJ-KANTOR-'),
        ]);
    }

    public function create() { return Inertia::render('BarangMasukKantor/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_jalan'          => 'nullable|string|max:100',
            'tanggal_kirim'           => 'required|date',
            'po'                      => 'required|string|max:100',
            'models'                  => 'required|array|min:1',
            'models.*.model'          => 'required|string|max:200',
            'models.*.pcs_barang_jadi' => 'required|integer|min:1',
        ]);
        foreach ($request->models as $m) {
            BarangMasukKantor::create([
                'no_surat_jalan'  => $request->no_surat_jalan,
                'tanggal_kirim'   => $request->tanggal_kirim,
                'po'              => $request->po,
                'model'           => $m['model'],
                'pcs_barang_jadi' => $m['pcs_barang_jadi'],
            ]);
        }
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
