<?php

namespace App\Http\Controllers;

use App\Models\StokBahan;
use App\Models\BahanMasuk;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StokBahanController extends Controller
{
    public function index()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');

        // Stok bahan gudang = barcode yang sudah lengkap TAPI belum masuk surat jalan garmen
        $query = \App\Models\BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->when($search, fn($q) => $q->where('kode_bahan', 'like', "%{$search}%"))
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', $namaBahan))
            ->when($supplier, fn($q) => $q->where('supplier', $supplier))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        // Daftar nama bahan unik untuk filter (hanya yang masih di gudang)
        $namaBahanOptions = \App\Models\BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->whereNotNull('nama_bahan')
            ->select('nama_bahan')
            ->distinct()
            ->orderBy('nama_bahan')
            ->pluck('nama_bahan');

        // Daftar supplier unik untuk filter
        $supplierOptions = \App\Models\BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

        return Inertia::render('StokBahan/Index', [
            'data' => $query,
            'namaBahanOptions' => $namaBahanOptions,
            'supplierOptions' => $supplierOptions,
            'filters' => [
                'search' => $search,
                'nama_bahan' => $namaBahan,
                'supplier' => $supplier,
            ],
        ]);
    }
}
