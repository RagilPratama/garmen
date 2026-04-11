<?php

namespace App\Http\Controllers;

use App\Models\JualGudang;
use App\Models\BarangMasukKantor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JualGudangController extends Controller
{
    public function index()
    {
        $data = JualGudang::latest()->paginate(15);

        // Stok gudang = total masuk kantor - sudah terjual dari gudang (lunas + pending), per model
        $masuk = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');
        $terjual = JualGudang::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        $stokOptions = $masuk->map(function ($row) use ($terjual) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            return ['model' => $row->model, 'sisa_stok' => $sisa];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('JualGudang/Index', ['data' => $data, 'stokOptions' => $stokOptions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_nota'      => 'nullable|string|max:100',
            'tanggal_nota' => 'required|date',
            'buyer'        => 'required|string|max:200',
            'po'           => 'nullable|string|max:100',
            'model'        => 'required|string|max:200',
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
            'status'       => 'required|string|in:pending,lunas,batal',
        ]);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        JualGudang::create($validated);
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, JualGudang $jualGudang)
    {
        $validated = $request->validate([
            'no_nota'      => 'nullable|string|max:100',
            'tanggal_nota' => 'required|date',
            'buyer'        => 'required|string|max:200',
            'po'           => 'nullable|string|max:100',
            'model'        => 'required|string|max:200',
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
            'status'       => 'required|string|in:pending,lunas,batal',
        ]);
        $validated['total_harga'] = $validated['pcs'] * $validated['harga_satuan'];
        $jualGudang->update($validated);
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(JualGudang $jualGudang)
    {
        $jualGudang->delete();
        return redirect()->route('jual-gudang.index')->with('message', 'Data berhasil dihapus.');
    }
}
