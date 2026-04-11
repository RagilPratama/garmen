<?php
namespace App\Http\Controllers;
use App\Models\BarangMasukKantor;
use App\Models\ProsesFinishing;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangMasukKantorController extends Controller
{
    use GeneratesSuratJalan;
    public function index()
    {
        $search = request('search');
        $data = BarangMasukKantor::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        $alreadyKantor = BarangMasukKantor::select('po', 'model')->get()
            ->map(fn($r) => $r->po . '|||' . $r->model)->toArray();
        $poOptions = ProsesFinishing::selectRaw("po, model, SUM(pcs_barang_jadi) as max_pcs")
            ->whereNotNull('pcs_barang_jadi')->where('pcs_barang_jadi', '>', 0)
            ->groupBy('po', 'model')->orderBy('po')->get()
            ->filter(fn($r) => !in_array($r->po . '|||' . $r->model, $alreadyKantor))->values();
        return Inertia::render('BarangMasukKantor/Index', ['data' => $data, 'poOptions' => $poOptions, 'nextSuratJalan' => $this->nextSuratJalan(BarangMasukKantor::class, 'SJ-KANTOR-')]);
    }
    public function create() { return Inertia::render('BarangMasukKantor/Form'); }
    public function store(Request $request) {
        $request->validate(['no_surat_jalan'=>'nullable|string|max:100','tanggal_kirim'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_barang_jadi'=>'required|integer|min:0']);
        BarangMasukKantor::create($request->all());
        return redirect()->route('barang-masuk-kantor.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(BarangMasukKantor $barangMasukKantor) { return Inertia::render('BarangMasukKantor/Form', ['item' => $barangMasukKantor]); }
    public function update(Request $request, BarangMasukKantor $barangMasukKantor) {
        $request->validate(['no_surat_jalan'=>'nullable|string|max:100','tanggal_kirim'=>'required|date','po'=>'required|string|max:100','model'=>'required|string|max:200','pcs_barang_jadi'=>'required|integer|min:0']);
        $barangMasukKantor->update($request->all());
        return redirect()->route('barang-masuk-kantor.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(BarangMasukKantor $barangMasukKantor) { $barangMasukKantor->delete(); return redirect()->route('barang-masuk-kantor.index')->with('message','Data berhasil dihapus.'); }
}
