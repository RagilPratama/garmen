<?php
namespace App\Http\Controllers;
use App\Models\ProsesJahit;
use App\Models\BahanProsesPotong;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesJahitController extends Controller
{
    public function index()
    {
        $data = ProsesJahit::latest()->paginate(15);
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
        $request->validate(['tanggal_jahit'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_potongan'=>'required|integer|min:0','tanggal_selesai_jahit'=>'nullable|date','pcs_hasil_jahit'=>'nullable|integer|min:0']);
        ProsesJahit::create($request->all());
        return redirect()->route('proses-jahit.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(ProsesJahit $prosesJahit) { return Inertia::render('ProsesJahit/Form', ['item' => $prosesJahit]); }
    public function update(Request $request, ProsesJahit $prosesJahit) {
        $request->validate(['tanggal_jahit'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_potongan'=>'required|integer|min:0','tanggal_selesai_jahit'=>'nullable|date','pcs_hasil_jahit'=>'nullable|integer|min:0']);
        $prosesJahit->update($request->all());
        return redirect()->route('proses-jahit.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(ProsesJahit $prosesJahit) { $prosesJahit->delete(); return redirect()->route('proses-jahit.index')->with('message','Data berhasil dihapus.'); }
}
