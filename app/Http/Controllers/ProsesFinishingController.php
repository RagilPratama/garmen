<?php
namespace App\Http\Controllers;
use App\Models\ProsesFinishing;
use App\Models\ProsesCuci;
use App\Models\Defect;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesFinishingController extends Controller
{
    public function index()
    {
        $search = request('search');
        $allRows = ProsesFinishing::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            $models = $rows->map(fn($r) => [
                'id'              => $r->id,
                'model'           => $r->model,
                'pcs'             => $r->pcs,
                'pcs_barang_jadi' => $r->pcs_barang_jadi,
                'tanggal_selesai' => $r->tanggal_selesai,
                'harga'           => $r->harga,
            ])->values();
            return [
                'po'              => $po,
                'tanggal_proses'  => $rows->min('tanggal_proses'),
                'jumlah_model'    => $rows->count(),
                'total_pcs'       => $rows->sum(fn($r) => (int)($r->pcs ?? 0)),
                'total_pcs_jadi'  => $rows->sum(fn($r) => (int)($r->pcs_barang_jadi ?? 0)),
                'models'          => $models,
            ];
        })->sortByDesc('tanggal_proses')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $alreadyFinishing = ProsesFinishing::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesCuci::selectRaw("po, model, SUM(pcs_kembali) as max_pcs")
            ->whereNotNull('pcs_kembali')->where('pcs_kembali', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyFinishing))->values();

        return Inertia::render('ProsesFinishing/Index', ['data' => $data, 'poOptions' => $poOptions]);
    }

    public function create() { return Inertia::render('ProsesFinishing/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'po'                       => 'required|string|max:100',
            'tanggal_proses'           => 'required|date',
            'models'                   => 'required|array|min:1',
            'models.*.model'           => 'required|string|max:200',
            'models.*.pcs'             => 'required|integer|min:0',
        ]);
        foreach ($request->models as $m) {
            $finishing = ProsesFinishing::create([
                'po'             => $request->po,
                'tanggal_proses' => $request->tanggal_proses,
                'model'          => $m['model'],
                'pcs'            => $m['pcs'],
                'pcs_barang_jadi' => $m['pcs_barang_jadi'] ?? null,
            ]);

            // Auto-catat defect jika pcs_barang_jadi < pcs
            $pcsBj = $m['pcs_barang_jadi'] ?? null;
            if (!is_null($pcsBj) && (int)$m['pcs'] > 0) {
                $selisih = (int)$m['pcs'] - (int)$pcsBj;
                if ($selisih > 0) {
                    Defect::updateOrCreate(
                        ['sumber' => 'finishing', 'referensi_id' => $finishing->id],
                        ['po' => $request->po, 'model' => $m['model'], 'pcs_defect' => $selisih,
                         'keterangan' => 'Pcs masuk: '.$m['pcs'].', barang jadi: '.(int)$pcsBj]
                    );
                }
            }
        }
        return redirect()->route('proses-finishing.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(ProsesFinishing $prosesFinishing) { return Inertia::render('ProsesFinishing/Form', ['item' => $prosesFinishing]); }

    public function update(Request $request, ProsesFinishing $prosesFinishing)
    {
        $request->validate([
            'tanggal_selesai'  => 'nullable|date',
            'pcs_barang_jadi'  => 'nullable|integer|min:1',
            'harga'            => 'nullable|integer|min:0',
        ]);
        $prosesFinishing->update($request->only(['tanggal_selesai', 'pcs_barang_jadi', 'harga']));

        // Auto-catat defect jika pcs_barang_jadi < pcs
        if ($request->filled('pcs_barang_jadi') && $prosesFinishing->pcs > 0) {
            $selisih = $prosesFinishing->pcs - (int)$request->pcs_barang_jadi;
            if ($selisih > 0) {
                Defect::updateOrCreate(
                    ['sumber' => 'finishing', 'referensi_id' => $prosesFinishing->id],
                    ['po' => $prosesFinishing->po, 'model' => $prosesFinishing->model, 'pcs_defect' => $selisih,
                     'keterangan' => 'Pcs masuk: '.$prosesFinishing->pcs.', barang jadi: '.(int)$request->pcs_barang_jadi]
                );
            } else {
                Defect::where('sumber', 'finishing')->where('referensi_id', $prosesFinishing->id)->delete();
            }
        }

        return redirect()->route('proses-finishing.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(ProsesFinishing $prosesFinishing)
    {
        $prosesFinishing->delete();
        return redirect()->route('proses-finishing.index')->with('message', 'Data berhasil dihapus.');
    }
}
