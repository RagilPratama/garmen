<?php
namespace App\Http\Controllers;
use App\Models\BarangKirimToko;
use App\Models\BarangMasukKantor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangKirimTokoController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = BarangKirimToko::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        $alreadyToko = BarangKirimToko::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = BarangMasukKantor::selectRaw("po, model, SUM(pcs_barang_jadi) as max_pcs")
            ->where('pcs_barang_jadi', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyToko))->values();
        return Inertia::render('BarangKirimToko/Index', ['data' => $data, 'poOptions' => $poOptions]);
    }
    public function create() { return Inertia::render('BarangKirimToko/Form'); }
    public function store(Request $request) {
        $request->validate(['no_surat_jalan'=>'nullable|string|max:100','tanggal_kirim'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_barang_jadi'=>'required|integer|min:0']);
        BarangKirimToko::create($request->all());
        return redirect()->route('barang-kirim-toko.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(BarangKirimToko $barangKirimToko) { return Inertia::render('BarangKirimToko/Form', ['item' => $barangKirimToko]); }
    public function update(Request $request, BarangKirimToko $barangKirimToko) {
        $request->validate(['no_surat_jalan'=>'nullable|string|max:100','tanggal_kirim'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_barang_jadi'=>'required|integer|min:0']);
        $barangKirimToko->update($request->all());
        return redirect()->route('barang-kirim-toko.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(BarangKirimToko $barangKirimToko) { $barangKirimToko->delete(); return redirect()->route('barang-kirim-toko.index')->with('message','Data berhasil dihapus.'); }
}
