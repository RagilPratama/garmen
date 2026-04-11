<?php
namespace App\Http\Controllers;
use App\Models\ProsesJual;
use App\Models\BarangKirimToko;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProsesJualController extends Controller
{
    public function index()
    {
        $data = ProsesJual::latest()->paginate(15);

        // Stok toko = total dikirim ke toko - total terjual (lunas + pending), per model
        $dikirim = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');
        $terjual = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        $stokOptions = $dikirim->map(function ($row) use ($terjual) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            return ['model' => $row->model, 'sisa_stok' => $sisa];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('ProsesJual/Index', ['data' => $data, 'stokOptions' => $stokOptions]);
    }
    public function create() { return Inertia::render('ProsesJual/Form'); }
    public function store(Request $request) {
        $validated = $request->validate(['no_nota'=>'nullable|string|max:100','tanggal_nota'=>'required|date','buyer'=>'required|string|max:200','model'=>'required|string|max:200','pcs'=>'required|integer|min:0','harga_satuan'=>'required|numeric|min:0','status'=>'required|string|in:pending,lunas,batal']);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        ProsesJual::create($validated);
        return redirect()->route('proses-jual.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(ProsesJual $prosesJual) { return Inertia::render('ProsesJual/Form', ['item' => $prosesJual]); }
    public function update(Request $request, ProsesJual $prosesJual) {
        $validated = $request->validate(['no_nota'=>'nullable|string|max:100','tanggal_nota'=>'required|date','buyer'=>'required|string|max:200','model'=>'required|string|max:200','pcs'=>'required|integer|min:0','harga_satuan'=>'required|numeric|min:0','status'=>'required|string|in:pending,lunas,batal']);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        $prosesJual->update($validated);
        return redirect()->route('proses-jual.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(ProsesJual $prosesJual) { $prosesJual->delete(); return redirect()->route('proses-jual.index')->with('message','Data berhasil dihapus.'); }
}
