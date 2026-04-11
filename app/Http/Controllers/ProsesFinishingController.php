<?php
namespace App\Http\Controllers;
use App\Models\ProsesFinishing;
use App\Models\ProsesCuci;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesFinishingController extends Controller
{
    public function index()
    {
        $data = ProsesFinishing::latest()->paginate(15);
        $alreadyFinishing = ProsesFinishing::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesCuci::selectRaw("po, model, SUM(pcs_kembali) as max_pcs")
            ->whereNotNull('pcs_kembali')->where('pcs_kembali', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyFinishing))->values();
        return Inertia::render('ProsesFinishing/Index', ['data' => $data, 'poOptions' => $poOptions]);
    }
    public function create() { return Inertia::render('ProsesFinishing/Form'); }
    public function store(Request $request) {
        $request->validate(['po'=>'required|string|max:100','model'=>'required|string|max:200','tanggal_proses'=>'required|date','pcs'=>'required|integer|min:0','tanggal_selesai'=>'nullable|date','pcs_barang_jadi'=>'nullable|integer|min:0']);
        ProsesFinishing::create($request->all());
        return redirect()->route('proses-finishing.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(ProsesFinishing $prosesFinishing) { return Inertia::render('ProsesFinishing/Form', ['item' => $prosesFinishing]); }
    public function update(Request $request, ProsesFinishing $prosesFinishing) {
        $request->validate(['po'=>'required|string|max:100','model'=>'required|string|max:200','tanggal_proses'=>'required|date','pcs'=>'required|integer|min:0','tanggal_selesai'=>'nullable|date','pcs_barang_jadi'=>'nullable|integer|min:0']);
        $prosesFinishing->update($request->all());
        return redirect()->route('proses-finishing.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(ProsesFinishing $prosesFinishing) { $prosesFinishing->delete(); return redirect()->route('proses-finishing.index')->with('message','Data berhasil dihapus.'); }
}
