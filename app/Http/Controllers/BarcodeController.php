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
            'tanggal' => 'required|date'
        ]);

        try {
            // Use database transaction
            $barcode = \DB::transaction(function () use ($validated) {
                // Generate kode_bahan (A0001, A0002, etc)
                $kodeBahan = $this->generateKodeBahan();

                // Use kode_bahan as barcode_code
                $validated['barcode_code'] = $kodeBahan;
                $validated['kode_bahan'] = $kodeBahan;

                // Set defaults for optional fields
                $validated['harga_sudah_diisi'] = false;
                $validated['no_surat_jalan'] = null;
                $validated['supplier'] = null;
                $validated['nama_bahan'] = null;
                $validated['quantity'] = null;
                $validated['satuan'] = null;
                $validated['rp_per_yard'] = null;
                $validated['total_harga'] = null;

                return BarcodeBahan::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Barcode berhasil disimpan',
                'data' => $barcode
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating barcode: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate kode bahan dengan format A0001 - Z9999
     * A0001 -> A9999 -> B0001 -> B9999 -> ... -> Z9999
     */
    private function generateKodeBahan()
    {
        // Get the latest barcode_code (not kode_bahan)
        $latestBarcode = BarcodeBahan::orderBy('barcode_code', 'desc')
            ->first();

        if (!$latestBarcode || !$latestBarcode->barcode_code) {
            // First code
            return 'A0001';
        }

        $lastCode = $latestBarcode->barcode_code;
        
        // Extract letter and number
        // Format: A0001 (1 letter + 4 digits)
        preg_match('/^([A-Z])(\d{4})$/', $lastCode, $matches);
        
        if (!$matches) {
            // If format doesn't match, start from A0001
            return 'A0001';
        }

        $letter = $matches[1];
        $number = (int)$matches[2];

        // Increment number
        $number++;

        // If number exceeds 9999, move to next letter
        if ($number > 9999) {
            $number = 1;
            $letter = chr(ord($letter) + 1);
            
            // If letter exceeds Z, start over from A (or throw error)
            if ($letter > 'Z') {
                throw new \Exception('Kode bahan sudah mencapai batas maksimum (Z9999). Silakan hubungi administrator.');
            }
        }

        $newCode = $letter . str_pad($number, 4, '0', STR_PAD_LEFT);
        
        // Double check if code already exists (safety check)
        $maxRetries = 10;
        $retryCount = 0;
        
        while (BarcodeBahan::where('barcode_code', $newCode)->exists() && $retryCount < $maxRetries) {
            // If exists, increment again
            $number++;
            if ($number > 9999) {
                $number = 1;
                $letter = chr(ord($letter) + 1);
                if ($letter > 'Z') {
                    throw new \Exception('Kode bahan sudah mencapai batas maksimum (Z9999)');
                }
            }
            $newCode = $letter . str_pad($number, 4, '0', STR_PAD_LEFT);
            $retryCount++;
        }
        
        if ($retryCount >= $maxRetries) {
            throw new \Exception('Gagal generate kode unik setelah ' . $maxRetries . ' percobaan');
        }

        return $newCode;
    }

    public function scan()
    {
        $suppliers = Supplier::orderBy('nama')->get(['id', 'nama']);
        $masterBahan = \App\Models\MasterBahan::orderBy('nama_bahan')->get(['id', 'nama_bahan']);
        
        return Inertia::render('Barcode/Scan', [
            'suppliers' => $suppliers,
            'masterBahan' => $masterBahan,
        ]);
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
        
        // Get unique suppliers for filter (exclude null/empty)
        $suppliers = BarcodeBahan::select('supplier')
            ->whereNotNull('supplier')
            ->where('supplier', '!=', '')
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

    public function updateComplete(Request $request, $id)
    {
        $validated = $request->validate([
            'supplier' => 'required|string',
            'nama_bahan' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'satuan' => 'nullable|string',
            'rp_per_yard' => 'required|numeric|min:0',
            'no_surat_jalan' => 'nullable|string'
        ]);

        $barcode = BarcodeBahan::findOrFail($id);
        
        // Update all fields
        $barcode->supplier = $validated['supplier'];
        $barcode->nama_bahan = $validated['nama_bahan'];
        $barcode->quantity = $validated['quantity'];
        $barcode->satuan = $validated['satuan'] ?? 'yard';
        $barcode->rp_per_yard = $validated['rp_per_yard'];
        $barcode->no_surat_jalan = $validated['no_surat_jalan'];
        $barcode->total_harga = $validated['quantity'] * $validated['rp_per_yard'];
        $barcode->harga_sudah_diisi = true;
        $barcode->save();

        return response()->json([
            'success' => true,
            'message' => 'Data lengkap berhasil disimpan',
            'data' => $barcode
        ]);
    }
}

