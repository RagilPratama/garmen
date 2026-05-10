<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\BarcodeBahan;
use Inertia\Inertia;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode_code' => 'required|unique:barcode_bahan,barcode_code',
            'no_surat_jalan' => 'nullable|string',
            'supplier' => 'required|string',
            'kode_bahan' => 'required|string',
            'nama_bahan' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'satuan' => 'nullable|string',
            'rp_per_yard' => 'nullable|numeric|min:0',
            'tanggal' => 'required|date'
        ]);

        // Calculate total if price exists
        if ($validated['rp_per_yard']) {
            $validated['total_harga'] = $validated['quantity'] * $validated['rp_per_yard'];
            $validated['harga_sudah_diisi'] = true;
        } else {
            $validated['harga_sudah_diisi'] = false;
        }

        $barcode = BarcodeBahan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil disimpan',
            'data' => $barcode
        ]);
    }

    public function scan()
    {
        return Inertia::render('Barcode/Scan');
    }

    public function list(Request $request)
    {
        $query = BarcodeBahan::query();

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'sudah_harga') {
                $query->where('harga_sudah_diisi', true);
            } elseif ($request->status === 'belum_harga') {
                $query->where('harga_sudah_diisi', false);
            }
        }

        // Filter by supplier
        if ($request->has('supplier') && $request->supplier) {
            $query->where('supplier', $request->supplier);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode_code', 'like', "%{$search}%")
                  ->orWhere('kode_bahan', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        $barcodes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get unique suppliers for filter
        $suppliers = BarcodeBahan::select('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

        // Statistics
        $stats = [
            'total' => BarcodeBahan::count(),
            'sudah_harga' => BarcodeBahan::where('harga_sudah_diisi', true)->count(),
            'belum_harga' => BarcodeBahan::where('harga_sudah_diisi', false)->count(),
        ];

        return Inertia::render('Barcode/List', [
            'barcodes' => $barcodes,
            'suppliers' => $suppliers,
            'stats' => $stats,
            'filters' => $request->only(['status', 'supplier', 'search'])
        ]);
    }

    public function findByCode(Request $request)
    {
        $request->validate([
            'barcode_code' => 'required|string'
        ]);

        $barcode = BarcodeBahan::where('barcode_code', $request->barcode_code)->first();

        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $barcode
        ]);
    }

    public function updatePrice(Request $request, $id)
    {
        $validated = $request->validate([
            'rp_per_yard' => 'required|numeric|min:0'
        ]);

        $barcode = BarcodeBahan::findOrFail($id);
        
        $barcode->rp_per_yard = $validated['rp_per_yard'];
        $barcode->total_harga = $barcode->quantity * $validated['rp_per_yard'];
        $barcode->harga_sudah_diisi = true;
        $barcode->save();

        return response()->json([
            'success' => true,
            'message' => 'Harga berhasil diupdate',
            'data' => $barcode
        ]);
    }
}

