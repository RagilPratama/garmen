<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RekeningController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = Rekening::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('bank',             'ilike', "%{$search}%")
                ->orWhere('nama',           'ilike', "%{$search}%")
                ->orWhere('nomor_rekening', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();

        return Inertia::render('Rekening/Index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank'            => 'required|string|max:100',
            'nama'            => 'required|string|max:200',
            'nomor_rekening'  => 'nullable|string|max:100',
        ]);

        Rekening::create($validated);

        return redirect()->route('rekening.index')->with('message', 'Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, Rekening $rekening)
    {
        $validated = $request->validate([
            'bank'            => 'required|string|max:100',
            'nama'            => 'required|string|max:200',
            'nomor_rekening'  => 'nullable|string|max:100',
        ]);

        $rekening->update($validated);

        return redirect()->route('rekening.index')->with('message', 'Rekening berhasil diperbarui.');
    }

    public function destroy(Rekening $rekening)
    {
        $rekening->delete();

        return redirect()->route('rekening.index')->with('message', 'Rekening berhasil dihapus.');
    }
}
