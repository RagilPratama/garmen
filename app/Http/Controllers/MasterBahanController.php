<?php

namespace App\Http\Controllers;

use App\Models\MasterBahan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterBahanController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterBahan::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_bahan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('nama_bahan')->paginate(50);

        return Inertia::render('MasterBahan/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255|unique:master_bahan,nama_bahan',
            'keterangan' => 'nullable|string|max:500',
        ]);

        MasterBahan::create($validated);

        return redirect()->route('master-bahan.index')->with('message', 'Master Bahan berhasil ditambahkan.');
    }

    public function update(Request $request, MasterBahan $masterBahan)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255|unique:master_bahan,nama_bahan,' . $masterBahan->id,
            'keterangan' => 'nullable|string|max:500',
        ]);

        $masterBahan->update($validated);

        return redirect()->route('master-bahan.index')->with('message', 'Master Bahan berhasil diupdate.');
    }

    public function destroy(MasterBahan $masterBahan)
    {
        $masterBahan->delete();

        return redirect()->route('master-bahan.index')->with('message', 'Master Bahan berhasil dihapus.');
    }
}
