<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Inertia\Inertia;

class BarcodeController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('nama')->get(['id', 'nama']);
        
        // Get bahan history grouped by supplier
        $bahanHistory = \DB::table('bahan_masuk')
            ->select('supplier', 'kode_bahan', 'nama_bahan', 'rp_per_yard')
            ->whereNotNull('kode_bahan')
            ->whereNotNull('supplier')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->groupBy('supplier')
            ->map(function ($items) {
                // Get unique kode_bahan with latest data
                return $items->unique('kode_bahan')->values();
            });
        
        return Inertia::render('Barcode/Index', [
            'suppliers' => $suppliers,
            'bahanHistory' => $bahanHistory
        ]);
    }
}
