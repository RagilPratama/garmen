<?php
namespace App\Http\Controllers;
use App\Models\ProsesCuci;
use App\Models\Defect;
use App\Models\ProsesJahit;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesCuciController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');
        $allRows = ProsesCuci::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('no_surat_jalan', 'like', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            $models = $rows->map(fn($r) => [
                'id'                       => $r->id,
                'model'                    => $r->model,
                'pcs_kirim'                => $r->pcs_kirim,
                'pcs_kembali'              => $r->pcs_kembali,
                'tanggal_kembali_dari_cuci' => $r->tanggal_kembali_dari_cuci,
            ])->values();
            return [
                'po'                => $po,
                'tanggal_kirim_cuci' => $rows->min('tanggal_kirim_cuci'),
                'no_surat_jalan'    => $rows->first()->no_surat_jalan,
                'jumlah_model'      => $rows->count(),
                'total_pcs_kirim'   => $rows->sum(fn($r) => (int)($r->pcs_kirim ?? 0)),
                'total_pcs_kembali' => $rows->sum(fn($r) => (int)($r->pcs_kembali ?? 0)),
                'models'            => $models,
            ];
        })->sortByDesc('tanggal_kirim_cuci')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $alreadyCuci = ProsesCuci::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesJahit::selectRaw("po, model, SUM(pcs_hasil_jahit) as max_pcs")
            ->whereNotNull('pcs_hasil_jahit')->where('pcs_hasil_jahit', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyCuci))->values();

        return Inertia::render('ProsesCuci/Index', [
            'data'          => $data,
            'poOptions'     => $poOptions,
            'nextSuratJalan' => $this->nextSuratJalan(ProsesCuci::class, 'SJ-CUCI-'),
        ]);
    }

    public function create() { return Inertia::render('ProsesCuci/Form'); }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_kirim_cuci'       => 'required|date',
            'no_surat_jalan'           => 'nullable|string|max:100',
            'po'                       => 'required|string|max:100',
            'models'                   => 'required|array|min:1',
            'models.*.model'           => 'required|string|max:200',
            'models.*.pcs_kirim'       => 'required|integer|min:1',
        ]);
        foreach ($request->models as $m) {
            $cuci = ProsesCuci::create([
                'tanggal_kirim_cuci' => $request->tanggal_kirim_cuci,
                'no_surat_jalan'     => $request->no_surat_jalan,
                'po'                 => $request->po,
                'model'              => $m['model'],
                'pcs_kirim'          => $m['pcs_kirim'],
                'pcs_kembali'        => $m['pcs_kembali'] ?? null,
            ]);

            // Auto-catat defect jika pcs_kembali < pcs_kirim
            $pcsKembali = $m['pcs_kembali'] ?? null;
            if (!is_null($pcsKembali) && (int)$m['pcs_kirim'] > 0) {
                $selisih = (int)$m['pcs_kirim'] - (int)$pcsKembali;
                if ($selisih > 0) {
                    Defect::updateOrCreate(
                        ['sumber' => 'cuci', 'referensi_id' => $cuci->id],
                        ['po' => $request->po, 'model' => $m['model'], 'pcs_defect' => $selisih,
                         'keterangan' => 'Pcs kirim: '.$m['pcs_kirim'].', kembali: '.(int)$pcsKembali]
                    );
                }
            }
        }
        return redirect()->route('proses-cuci.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(ProsesCuci $prosesCuci) { return Inertia::render('ProsesCuci/Form', ['item' => $prosesCuci]); }

    public function update(Request $request, ProsesCuci $prosesCuci)
    {
        $request->validate([
            'tanggal_kembali_dari_cuci' => 'nullable|date',
            'pcs_kembali'               => 'nullable|integer|min:0',
        ]);
        $prosesCuci->update($request->only(['tanggal_kembali_dari_cuci', 'pcs_kembali']));

        // Auto-catat defect jika pcs_kembali < pcs_kirim
        if ($request->filled('pcs_kembali') && $prosesCuci->pcs_kirim > 0) {
            $selisih = $prosesCuci->pcs_kirim - (int)$request->pcs_kembali;
            if ($selisih > 0) {
                Defect::updateOrCreate(
                    ['sumber' => 'cuci', 'referensi_id' => $prosesCuci->id],
                    ['po' => $prosesCuci->po, 'model' => $prosesCuci->model, 'pcs_defect' => $selisih,
                     'keterangan' => 'Pcs kirim: '.$prosesCuci->pcs_kirim.', kembali: '.(int)$request->pcs_kembali]
                );
            } else {
                Defect::where('sumber', 'cuci')->where('referensi_id', $prosesCuci->id)->delete();
            }
        }

        return redirect()->route('proses-cuci.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(ProsesCuci $prosesCuci)
    {
        $prosesCuci->delete();
        return redirect()->route('proses-cuci.index')->with('message', 'Data berhasil dihapus.');
    }
}
