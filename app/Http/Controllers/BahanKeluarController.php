<?php

namespace App\Http\Controllers;

use App\Models\BahanKeluar;
use App\Models\StokBahan;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BahanKeluarController extends Controller
{
    use GeneratesSuratJalan;
    public function index()
    {
        $search = request('search');
        $data = BahanKeluar::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('kode_bahan', 'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        $stok = StokBahan::orderBy('kode_bahan')->where('sisa_stok', '>', 0)->get(['kode_bahan', 'sisa_stok']);
        return Inertia::render('BahanKeluar/Index', ['data' => $data, 'stok' => $stok, 'nextSuratJalan' => $this->nextSuratJalan(BahanKeluar::class, 'BK-')]);
    }

    public function create()
    {
        return Inertia::render('BahanKeluar/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'        => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'kode_bahan'     => 'required|string|max:100',
            'yard'           => 'required|numeric|min:0',
        ]);

        $this->validateStok($validated['kode_bahan'], $validated['yard']);

        BahanKeluar::create($validated);
        $this->kurangiStok($validated['kode_bahan'], $validated['yard']);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BahanKeluar $bahanKeluar)
    {
        return Inertia::render('BahanKeluar/Form', ['item' => $bahanKeluar]);
    }

    public function update(Request $request, BahanKeluar $bahanKeluar)
    {
        $validated = $request->validate([
            'tanggal'        => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'kode_bahan'     => 'required|string|max:100',
            'yard'           => 'required|numeric|min:0',
        ]);

        $oldKode = $bahanKeluar->kode_bahan;
        $oldYard = $bahanKeluar->yard;

        // Reverse old deduction, check new, apply new
        $this->kurangiStok($oldKode, -$oldYard);
        $this->validateStok($validated['kode_bahan'], $validated['yard']);
        $bahanKeluar->update($validated);
        $this->kurangiStok($validated['kode_bahan'], $validated['yard']);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(BahanKeluar $bahanKeluar)
    {
        $this->kurangiStok($bahanKeluar->kode_bahan, -$bahanKeluar->yard);
        $bahanKeluar->delete();
        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil dihapus.');
    }

    private function validateStok(string $kodeBahan, float $yard): void
    {
        $stok = StokBahan::where('kode_bahan', $kodeBahan)->first();
        $sisa = $stok?->sisa_stok ?? 0;
        if ($yard > $sisa) {
            throw ValidationException::withMessages([
                'yard' => "Stok tidak cukup. Sisa stok {$kodeBahan}: {$sisa} yard.",
            ]);
        }
    }

    private function kurangiStok(string $kodeBahan, float $yard): void
    {
        $stok = StokBahan::firstOrCreate(
            ['kode_bahan' => $kodeBahan],
            ['total_masuk' => 0, 'total_keluar' => 0, 'sisa_stok' => 0]
        );
        $stok->total_keluar = max(0, $stok->total_keluar + $yard);
        $stok->sisa_stok    = max(0, $stok->total_masuk - $stok->total_keluar);
        $stok->save();
    }
}

