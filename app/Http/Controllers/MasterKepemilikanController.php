<?php

namespace App\Http\Controllers;

use App\Models\MasterKepemilikan;
use Illuminate\Http\Request;

class MasterKepemilikanController extends Controller
{
    public function index()
    {
        $data = MasterKepemilikan::query()->when(request('search'), fn($q, $search) => $q->where('nama_kepemilikan', 'like', "%{$search}%"))->latest()->paginate(10);

        return inertia('MasterKepemilikan/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kepemilikan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        MasterKepemilikan::create($validated);

        return back()->with('message', 'Kepemilikan berhasil ditambahkan');
    }

    public function update(Request $request, MasterKepemilikan $masterKepemilikan)
    {
        $validated = $request->validate([
            'nama_kepemilikan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $masterKepemilikan->update($validated);

        return back()->with('message', 'Kepemilikan berhasil diupdate');
    }

    public function destroy(MasterKepemilikan $masterKepemilikan)
    {
        $masterKepemilikan->delete();

        return back()->with('message', 'Kepemilikan berhasil dihapus');
    }
}
