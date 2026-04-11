<?php
namespace App\Http\Controllers;
use App\Models\ProsesCuci;
use App\Models\ProsesJahit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProsesCuciController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = ProsesCuci::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        $alreadyCuci = ProsesCuci::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesJahit::selectRaw("po, model, SUM(pcs_hasil_jahit) as max_pcs")
            ->whereNotNull('pcs_hasil_jahit')->where('pcs_hasil_jahit', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyCuci))->values();
        return Inertia::render('ProsesCuci/Index', ['data' => $data, 'poOptions' => $poOptions]);
    }
    public function create() { return Inertia::render('ProsesCuci/Form'); }
    public function store(Request $request) {
        $request->validate(['tanggal_kirim_cuci'=>'required|date','no_surat_jalan'=>'nullable|string|max:100','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_kirim'=>'required|integer|min:0','tanggal_kembali_dari_cuci'=>'nullable|date','pcs_kembali'=>'nullable|integer|min:0']);
        ProsesCuci::create($request->all());
        return redirect()->route('proses-cuci.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(ProsesCuci $prosesCuci) { return Inertia::render('ProsesCuci/Form', ['item' => $prosesCuci]); }
    public function update(Request $request, ProsesCuci $prosesCuci) {
        $request->validate(['tanggal_kirim_cuci'=>'required|date','no_surat_jalan'=>'nullable|string|max:100','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_kirim'=>'required|integer|min:0','tanggal_kembali_dari_cuci'=>'nullable|date','pcs_kembali'=>'nullable|integer|min:0']);
        $prosesCuci->update($request->all());
        return redirect()->route('proses-cuci.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(ProsesCuci $prosesCuci) { $prosesCuci->delete(); return redirect()->route('proses-cuci.index')->with('message','Data berhasil dihapus.'); }
}
