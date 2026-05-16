<?php

namespace App\Http\Controllers;

use App\Models\SuratJalanGarmenItem;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StokBahanGarmenController extends Controller
{
    public function index()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');

        // Stok bahan garmen = item yang sudah masuk via surat jalan
        $query = SuratJalanGarmenItem::with('suratJalan')
            ->when($search, fn($q) => $q->where('kode_bahan', 'like', "%{$search}%"))
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', $namaBahan))
            ->when($supplier, fn($q) => $q->where('supplier', $supplier))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(request()->query());

        // Transform untuk tambahkan no_surat_jalan
        $query->getCollection()->transform(function ($item) {
            $item->no_surat_jalan = $item->suratJalan->no_surat_jalan ?? '—';
            $item->tanggal_kirim = $item->suratJalan->tanggal ?? null;
            return $item;
        });

        // Daftar nama bahan unik untuk filter
        $namaBahanOptions = SuratJalanGarmenItem::whereNotNull('nama_bahan')
            ->select('nama_bahan')
            ->distinct()
            ->orderBy('nama_bahan')
            ->pluck('nama_bahan');

        // Daftar supplier unik untuk filter
        $supplierOptions = SuratJalanGarmenItem::whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

        return Inertia::render('StokBahanGarmen/Index', [
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
