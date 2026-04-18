<?php
namespace App\Http\Controllers;
use App\Models\ProsesJahit;
use App\Models\Defect;
use App\Models\BahanProsesPotong;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesJahitController extends Controller
{
    public function index()
    {
        $search = request('search');

        $allRows = ProsesJahit::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
            ))->get();

        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            $totalHasil = $rows->sum(fn($r) => (int)($r->pcs_hasil_jahit ?? 0));
            $models = $rows->map(fn($r) => [
                'id'                    => $r->id,
                'model'                 => $r->model,
                'pcs_potongan'          => $r->pcs_potongan,
                'pcs_hasil_jahit'       => $r->pcs_hasil_jahit,
                'tanggal_selesai_jahit' => $r->tanggal_selesai_jahit,
            ])->values();
            return [
                'po'                    => $po,
                'tanggal_jahit'         => $rows->min('tanggal_jahit'),
                'jumlah_model'          => $rows->count(),
                'total_pcs_hasil_jahit' => $totalHasil,
                'models'                => $models,
            ];
        })->sortByDesc('tanggal_jahit')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $alreadyJahit = ProsesJahit::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = BahanProsesPotong::selectRaw("po, model, SUM(hasil_potongan) as max_pcs")
            ->whereNotNull('hasil_potongan')->where('hasil_potongan', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyJahit))->values();
        return Inertia::render('ProsesJahit/Index', ['data' => $data, 'poOptions' => $poOptions]);
    }
    public function create() { return Inertia::render('ProsesJahit/Form'); }
    public function store(Request $request) {
        $request->validate([
            'tanggal_jahit'              => 'required|date',
            'po'                         => 'required|string|max:100',
            'tanggal_selesai_jahit'      => 'nullable|date',
            'models'                     => 'required|array|min:1',
            'models.*.model'             => 'required|string|max:200',
            'models.*.pcs_potongan'      => 'required|integer|min:0',
            'models.*.pcs_hasil_jahit'   => 'nullable|integer|min:0',
        ]);
        foreach ($request->models as $m) {
            $jahit = ProsesJahit::create([
                'tanggal_jahit'         => $request->tanggal_jahit,
                'po'                    => $request->po,
                'tanggal_selesai_jahit' => $request->tanggal_selesai_jahit,
                'model'                 => $m['model'],
                'pcs_potongan'          => $m['pcs_potongan'],
                'pcs_hasil_jahit'       => $m['pcs_hasil_jahit'] ?? null,
            ]);

            // Auto-catat defect jika pcs_hasil_jahit < pcs_potongan
            $hasilJahit = $m['pcs_hasil_jahit'] ?? null;
            if (!is_null($hasilJahit) && (int)$m['pcs_potongan'] > 0) {
                $selisih = (int)$m['pcs_potongan'] - (int)$hasilJahit;
                if ($selisih > 0) {
                    Defect::updateOrCreate(
                        ['sumber' => 'jahit', 'referensi_id' => $jahit->id],
                        ['po' => $request->po, 'model' => $m['model'], 'pcs_defect' => $selisih,
                         'keterangan' => 'Pcs potong: '.$m['pcs_potongan'].', hasil jahit: '.(int)$hasilJahit]
                    );
                }
            }
        }
        return redirect()->route('proses-jahit.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(ProsesJahit $prosesJahit) { return Inertia::render('ProsesJahit/Form', ['item' => $prosesJahit]); }
    public function update(Request $request, ProsesJahit $prosesJahit) {
        $request->validate(['tanggal_jahit'=>'required|date','tanggal_selesai_jahit'=>'nullable|date','pcs_hasil_jahit'=>'nullable|integer|min:0']);
        $prosesJahit->update($request->only(['tanggal_jahit','tanggal_selesai_jahit','pcs_hasil_jahit']));

        // Auto-catat defect jika pcs_hasil_jahit < pcs_potongan
        if ($request->filled('pcs_hasil_jahit') && $prosesJahit->pcs_potongan > 0) {
            $selisih = $prosesJahit->pcs_potongan - (int)$request->pcs_hasil_jahit;
            if ($selisih > 0) {
                Defect::updateOrCreate(
                    ['sumber' => 'jahit', 'referensi_id' => $prosesJahit->id],
                    ['po' => $prosesJahit->po, 'model' => $prosesJahit->model, 'pcs_defect' => $selisih,
                     'keterangan' => 'Pcs potong: '.$prosesJahit->pcs_potongan.', hasil jahit: '.(int)$request->pcs_hasil_jahit]
                );
            } else {
                Defect::where('sumber', 'jahit')->where('referensi_id', $prosesJahit->id)->delete();
            }
        }

        return redirect()->route('proses-jahit.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(ProsesJahit $prosesJahit) { $prosesJahit->delete(); return redirect()->route('proses-jahit.index')->with('message','Data berhasil dihapus.'); }
}
