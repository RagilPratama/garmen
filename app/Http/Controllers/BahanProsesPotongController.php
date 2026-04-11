<?php
namespace App\Http\Controllers;
use App\Models\BahanProsesPotong;
use App\Models\BahanKeluar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BahanProsesPotongController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = BahanProsesPotong::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po', 'ilike', "%{$search}%")
                ->orWhere('model', 'ilike', "%{$search}%")
                ->orWhere('kode_bahan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        $bahanOptions = BahanKeluar::selectRaw('kode_bahan, SUM(yard) as total_yard')
            ->groupBy('kode_bahan')->orderBy('kode_bahan')->get();
        return Inertia::render('BahanProsesPotong/Index', ['data' => $data, 'bahanOptions' => $bahanOptions]);
    }
    public function create() { return Inertia::render('BahanProsesPotong/Form'); }
    public function store(Request $request) {
        $request->validate(['tanggal_potong'=>'required|date','yard'=>'required|numeric|min:0','po'=>'required|string|max:100','model'=>'required|string|max:200','kode_bahan'=>'required|string|max:100','hasil_potongan'=>'nullable|integer|min:0']);
        BahanProsesPotong::create($request->all());
        return redirect()->route('bahan-proses-potong.index')->with('message','Data berhasil ditambahkan.');
    }
    public function edit(BahanProsesPotong $bahanProsesPotong) { return Inertia::render('BahanProsesPotong/Form', ['item' => $bahanProsesPotong]); }
    public function update(Request $request, BahanProsesPotong $bahanProsesPotong) {
        $request->validate(['tanggal_potong'=>'required|date','yard'=>'required|numeric|min:0','po'=>'required|string|max:100','model'=>'required|string|max:200','kode_bahan'=>'required|string|max:100','hasil_potongan'=>'nullable|integer|min:0']);
        $bahanProsesPotong->update($request->all());
        return redirect()->route('bahan-proses-potong.index')->with('message','Data berhasil diperbarui.');
    }
    public function destroy(BahanProsesPotong $bahanProsesPotong) { $bahanProsesPotong->delete(); return redirect()->route('bahan-proses-potong.index')->with('message','Data berhasil dihapus.'); }
}
