<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $search = request('search');
        $data = Supplier::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('telepon', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
            ))->paginate(15)->withQueryString();
        return Inertia::render('Supplier/Index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'    => 'required|string|max:200',
            'telepon' => 'nullable|string|max:30',
            'alamat'  => 'nullable|string|max:500',
        ]);
        Supplier::create($validated);
        return redirect()->route('supplier.index')->with('message', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama'    => 'required|string|max:200',
            'telepon' => 'nullable|string|max:30',
            'alamat'  => 'nullable|string|max:500',
        ]);
        $supplier->update($validated);
        return redirect()->route('supplier.index')->with('message', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if (DB::table('bahan_masuk')->where('supplier', $supplier->nama)->exists()) {
            return back()->with('error', "Supplier \"{$supplier->nama}\" tidak dapat dihapus karena masih digunakan dalam data transaksi.");
        }

        $supplier->delete();
        return redirect()->route('supplier.index')->with('message', 'Supplier berhasil dihapus.');
    }
}
