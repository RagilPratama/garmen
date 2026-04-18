<?php

namespace App\Http\Controllers;

use App\Models\MasterModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MasterModelController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = MasterModel::select('id', 'nama_model', 'keterangan', 'created_at')
            ->latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('nama_model', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%")
            ))
            ->paginate(15)
            ->withQueryString();
        
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

        $oldName = $masterModel->nama_model;
        $newName = $validated['nama_model'];

        $masterModel->update($validated);

        // Cascade rename ke semua tabel transaksi yang menyimpan model sebagai string
        if ($oldName !== $newName) {
            $tables = [
                'bahan_proses_potong',
                'proses_jahit',
                'proses_cuci',
                'proses_finishing',
                'barang_masuk_kantor',
                'barang_kirim_toko',
                'proses_jual',
                'jual_gudang',
            ];
            foreach ($tables as $table) {
                DB::table($table)->where('model', $oldName)->update(['model' => $newName]);
            }
        }

        return redirect()->route('master-model.index')->with('message', 'Model berhasil diperbarui.');
    }

    public function destroy(MasterModel $masterModel)
    {
        $name   = $masterModel->nama_model;
        $tables = [
            'bahan_proses_potong',
            'proses_jahit',
            'proses_cuci',
            'proses_finishing',
            'barang_masuk_kantor',
            'barang_kirim_toko',
            'proses_jual',
            'jual_gudang',
        ];

        foreach ($tables as $table) {
            if (DB::table($table)->where('model', $name)->exists()) {
                return back()->with('error', "Model \"$name\" tidak dapat dihapus karena masih digunakan dalam data transaksi.");
            }
        }

        $masterModel->delete();
        return redirect()->route('master-model.index')->with('message', 'Model berhasil dihapus.');
    }
}
