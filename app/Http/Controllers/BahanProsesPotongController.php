<?php
namespace App\Http\Controllers;
use App\Models\BahanProsesPotong;
use App\Models\BahanKeluar;
use App\Models\MasterModel;
use App\Models\StokBahan;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BahanProsesPotongController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');

        $allRows = BahanProsesPotong::query()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
            ))
            ->orderByDesc('created_at')
            ->get();

        // Group by PO
        $grouped = $allRows->groupBy('po')->map(function ($rows, $po) {
            $modelGroups = $rows->groupBy('model');
            $totalHasil  = $modelGroups->sum(fn($mr) => (int)($mr->first()->hasil_potongan ?? 0));

            $models = $modelGroups->map(function ($mr, $model) {
                return [
                    'model'          => $model,
                    'hasil_potongan' => $mr->first()->hasil_potongan ?? 0,
                    'bahans'         => $mr->map(fn($r) => [
                        'id'         => $r->id,
                        'kode_bahan' => $r->kode_bahan,
                        'yard'       => $r->yard,
                    ])->values(),
                ];
            })->values();

            return [
                'po'                   => $po,
                'tanggal_potong'       => $rows->min('tanggal_potong'),
                'jumlah_model'         => $modelGroups->count(),
                'total_hasil_potongan' => $totalHasil,
                'models'               => $models,
            ];
        })->sortByDesc('tanggal_potong')->values();

        // Manual pagination
        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $bahanOptions = BahanKeluar::selectRaw('kode_bahan, SUM(yard) as total_yard')
            ->groupBy('kode_bahan')->orderBy('kode_bahan')->get();
        $modelOptions = MasterModel::orderBy('nama_model')->pluck('nama_model');
        $nextPo       = $this->nextCode(BahanProsesPotong::class, 'po', 'PO-');

        return Inertia::render('BahanProsesPotong/Index', [
            'data'         => $paginator,
            'bahanOptions' => $bahanOptions,
            'modelOptions' => $modelOptions,
            'nextPo'       => $nextPo,
        ]);
    }
    public function create() { return Inertia::render('BahanProsesPotong/Form'); }
    public function store(Request $request) {
        $request->validate([
            'tanggal_potong'                      => 'required|date',
            'po'                                  => 'required|string|max:100',
            'models'                              => 'required|array|min:1',
            'models.*.model'                      => 'required|string|max:200',
            'models.*.hasil_potongan'             => 'nullable|integer|min:0',
            'models.*.bahans'                     => 'required|array|min:1',
            'models.*.bahans.*.kode_bahan'        => 'required|string|max:100',
            'models.*.bahans.*.yard'              => 'required|numeric|min:0',
        ]);

        $rows = [];
        foreach ($request->models as $modelItem) {
            foreach ($modelItem['bahans'] as $bahan) {
                $rows[] = [
                    'tanggal_potong' => $request->tanggal_potong,
                    'po'             => $request->po,
                    'model'          => $modelItem['model'],
                    'hasil_potongan' => $modelItem['hasil_potongan'] ?? 0,
                    'kode_bahan'     => $bahan['kode_bahan'],
                    'yard'           => $bahan['yard'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
        }

        BahanProsesPotong::insert($rows);

        // Kurangi stok bahan sesuai yard yang digunakan
        foreach ($request->models as $modelItem) {
            foreach ($modelItem['bahans'] as $bahan) {
                $stok = StokBahan::where('kode_bahan', $bahan['kode_bahan'])->first();
                if ($stok) {
                    $stok->total_keluar += $bahan['yard'];
                    $stok->sisa_stok   -= $bahan['yard'];
                    $stok->save();
                }
            }
        }

        return redirect()->route('bahan-proses-potong.index')->with('message', 'Data berhasil ditambahkan.');
    }
    public function edit(BahanProsesPotong $bahanProsesPotong) { return Inertia::render('BahanProsesPotong/Form', ['item' => $bahanProsesPotong]); }
    public function update(Request $request, BahanProsesPotong $bahanProsesPotong) {
        $request->validate(['tanggal_potong'=>'required|date','yard'=>'required|numeric|min:0','po'=>'required|string|max:100','model'=>'required|string|max:200','kode_bahan'=>'required|string|max:100','hasil_potongan'=>'nullable|integer|min:0']);
        $bahanProsesPotong->update($request->only(['tanggal_potong','yard','po','model','kode_bahan','hasil_potongan']));
        return redirect()->route('bahan-proses-potong.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(BahanProsesPotong $bahanProsesPotong) {
        // Kembalikan stok ketika baris dihapus
        $stok = StokBahan::where('kode_bahan', $bahanProsesPotong->kode_bahan)->first();
        if ($stok) {
            $stok->total_keluar -= $bahanProsesPotong->yard;
            $stok->sisa_stok   += $bahanProsesPotong->yard;
            $stok->save();
        }
        $bahanProsesPotong->delete();
        return redirect()->route('bahan-proses-potong.index')->with('message','Data berhasil dihapus.');
    }

    public function updateModel(Request $request)
    {
        $request->validate([
            'tanggal_potong'           => 'required|date',
            'po'                       => 'required|string|max:100',
            'model'                    => 'required|string|max:200',
            'hasil_potongan'           => 'nullable|integer|min:0',
            'bahans'                   => 'required|array|min:1',
            'bahans.*.kode_bahan'      => 'required|string|max:100',
            'bahans.*.yard'            => 'required|numeric|min:0',
        ]);

        // Kembalikan stok dari baris lama sebelum dihapus
        $oldRows = BahanProsesPotong::where('po', $request->po)
            ->where('model', $request->model)
            ->get();
        foreach ($oldRows as $old) {
            $stok = StokBahan::where('kode_bahan', $old->kode_bahan)->first();
            if ($stok) {
                $stok->total_keluar -= $old->yard;
                $stok->sisa_stok   += $old->yard;
                $stok->save();
            }
        }

        // Delete all existing rows for this po+model combination
        BahanProsesPotong::where('po', $request->po)
            ->where('model', $request->model)
            ->delete();

        // Re-insert with updated data
        $rows = [];
        foreach ($request->bahans as $bahan) {
            $rows[] = [
                'tanggal_potong' => $request->tanggal_potong,
                'po'             => $request->po,
                'model'          => $request->model,
                'hasil_potongan' => $request->hasil_potongan ?? 0,
                'kode_bahan'     => $bahan['kode_bahan'],
                'yard'           => $bahan['yard'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        BahanProsesPotong::insert($rows);

        // Deduct stok dari baris baru
        foreach ($request->bahans as $bahan) {
            $stok = StokBahan::where('kode_bahan', $bahan['kode_bahan'])->first();
            if ($stok) {
                $stok->total_keluar += $bahan['yard'];
                $stok->sisa_stok   -= $bahan['yard'];
                $stok->save();
            }
        }

        return redirect()->route('bahan-proses-potong.index')->with('message', 'Data berhasil diperbarui.');
    }
}
