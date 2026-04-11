<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BahanMasukController extends Controller
{
    public function index()
    {
        $data = BahanMasuk::latest()->paginate(15);
        return Inertia::render('BahanMasuk/Index', ['data' => $data]);
    }

    public function create()
    {
        return Inertia::render('BahanMasuk/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'       => 'required|date',
            'no_surat_jalan'=> 'nullable|string|max:100',
            'no_nota'       => 'nullable|string|max:100',
            'supplier'      => 'required|string|max:200',
            'kode_bahan'    => 'required|string|max:100',
            'yard'          => 'required|numeric|min:0',
            'rp_per_yard'   => 'required|numeric|min:0',
            'status'        => 'required|string|in:pending,diterima,ditolak',
        ]);
        $validated['total'] = $validated['yard'] * $validated['rp_per_yard'];
        BahanMasuk::create($validated);

        if ($validated['status'] === 'diterima') {
            $this->addStok($validated['kode_bahan'], $validated['yard']);
        }

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BahanMasuk $bahanMasuk)
    {
        return Inertia::render('BahanMasuk/Form', ['item' => $bahanMasuk]);
    }

    public function update(Request $request, BahanMasuk $bahanMasuk)
    {
        $validated = $request->validate([
            'tanggal'       => 'required|date',
            'no_surat_jalan'=> 'nullable|string|max:100',
            'no_nota'       => 'nullable|string|max:100',
            'supplier'      => 'required|string|max:200',
            'kode_bahan'    => 'required|string|max:100',
            'yard'          => 'required|numeric|min:0',
            'rp_per_yard'   => 'required|numeric|min:0',
            'status'        => 'required|string|in:pending,diterima,ditolak',
        ]);
        $validated['total'] = $validated['yard'] * $validated['rp_per_yard'];

        $oldKode   = $bahanMasuk->kode_bahan;
        $oldYard   = $bahanMasuk->yard;
        $oldStatus = $bahanMasuk->status;

        $bahanMasuk->update($validated);

        // Reverse old contribution, then apply new
        if ($oldStatus === 'diterima') {
            $this->addStok($oldKode, -$oldYard);
        }
        if ($validated['status'] === 'diterima') {
            $this->addStok($validated['kode_bahan'], $validated['yard']);
        }

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(BahanMasuk $bahanMasuk)
    {
        if ($bahanMasuk->status === 'diterima') {
            $this->addStok($bahanMasuk->kode_bahan, -$bahanMasuk->yard);
        }
        $bahanMasuk->delete();
        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil dihapus.');
    }

    private function addStok(string $kodeBahan, float $yard): void
    {
        $stok = StokBahan::firstOrCreate(
            ['kode_bahan' => $kodeBahan],
            ['total_masuk' => 0, 'total_keluar' => 0, 'sisa_stok' => 0]
        );
        $stok->total_masuk = max(0, $stok->total_masuk + $yard);
        $stok->sisa_stok   = max(0, $stok->total_masuk - $stok->total_keluar);
        $stok->save();
    }
}

