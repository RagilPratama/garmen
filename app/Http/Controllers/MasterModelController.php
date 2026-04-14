<?php

namespace App\Http\Controllers;

use App\Models\MasterModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterModelController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = MasterModel::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('nama_model', 'ilike', "%{$search}%")
                ->orWhere('keterangan', 'ilike', "%{$search}%")
            ))->paginate(15)->withQueryString();
        return Inertia::render('MasterModel/Index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_model'  => 'required|string|max:200|unique:master_models,nama_model',
            'keterangan'  => 'nullable|string|max:500',
        ]);
        MasterModel::create($validated);
        return redirect()->route('master-model.index')->with('message', 'Model berhasil ditambahkan.');
    }

    public function update(Request $request, MasterModel $masterModel)
    {
        $validated = $request->validate([
            'nama_model'  => 'required|string|max:200|unique:master_models,nama_model,' . $masterModel->id,
            'keterangan'  => 'nullable|string|max:500',
        ]);
        $masterModel->update($validated);
        return redirect()->route('master-model.index')->with('message', 'Model berhasil diperbarui.');
    }

    public function destroy(MasterModel $masterModel)
    {
        $masterModel->delete();
        return redirect()->route('master-model.index')->with('message', 'Model berhasil dihapus.');
    }
}
