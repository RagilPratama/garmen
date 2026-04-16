<?php
namespace App\Http\Controllers;
use App\Models\ProsesJual;
use App\Models\BarangKirimToko;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProsesJualController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search  = request('search');
        $allRows = ProsesJual::latest()
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

        // Stok toko = total dikirim ke toko - total terjual (lunas + pending), per model

        // Stok toko = total dikirim ke toko - total terjual (lunas + pending), per model
        $dikirim = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');
        $terjual = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        // Ambil harga_satuan terakhir dari proses_finishing untuk setiap model
        $hargaFinishing = DB::table('proses_finishing')
            ->select('model', DB::raw('MAX(id) as max_id'))
            ->groupBy('model')->pluck('max_id', 'model');
        $hargaPerModel = [];
        if ($hargaFinishing->count()) {
            $hargaRows = DB::table('proses_finishing')
                ->whereIn('id', $hargaFinishing->values())
                ->pluck('harga_satuan', 'model');
            $hargaPerModel = $hargaRows;
        }

        $stokOptions = $dikirim->map(function ($row) use ($terjual, $hargaPerModel) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            return [
                'model'        => $row->model,
                'sisa_stok'    => $sisa,
                'harga_satuan' => isset($hargaPerModel[$row->model]) ? (int) $hargaPerModel[$row->model] : 0,
            ];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('ProsesJual/Index', [
            'data'        => $data,
            'stokOptions' => $stokOptions,
            'nextNota'    => $this->nextCode(ProsesJual::class, 'no_nota', 'NT-TOKO-'),
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
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();
        ProsesJual::insert($rows);
        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, ProsesJual $prosesJual)
    {
        $validated = $request->validate([
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        $prosesJual->update($validated);
        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function updateNotaStatus(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string|max:100',
            'status'  => 'required|string|in:pending,lunas,batal',
        ]);
        ProsesJual::where('no_nota', $request->no_nota)->update(['status' => $request->status]);
        return redirect()->route('proses-jual.index')->with('message', 'Status nota berhasil diperbarui.');
    }

    public function destroy(ProsesJual $prosesJual)
    {
        $prosesJual->delete();
        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil dihapus.');
    }
}
