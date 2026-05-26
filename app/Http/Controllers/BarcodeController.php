<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\BarcodeBahan;
use App\Models\BahanMasuk;
use App\Traits\GeneratesSuratJalan;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    use GeneratesSuratJalan;
    public function index()
    {
        $suppliers = Supplier::orderBy('nama', 'asc')->get(['id', 'nama']);

        // Get bahan history grouped by supplier
        $bahanHistory = DB::table('bahan_masuk')
            ->select('supplier', 'kode_bahan', 'nama_bahan', 'harga_satuan', 'satuan')
            ->whereNotNull('kode_bahan')
            ->whereNotNull('supplier')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->groupBy('supplier')
            ->map(function ($items) {
                // Get unique kode_bahan with latest data
                return $items->unique('kode_bahan')->values();
            });

        // Get barcode yang belum lengkap (belum diisi harga/supplier)
        $belumLengkap = BarcodeBahan::where('harga_sudah_diisi', '=', false, 'and')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Barcode/Index', [
            'suppliers' => $suppliers,
            'bahanHistory' => $bahanHistory,
            'belumLengkap' => $belumLengkap,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
        ]);

        try {
            // Use database transaction
            $barcode = DB::transaction(function () use ($validated) {
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

                $barcode = BarcodeBahan::create($validated);

                DB::statement(
                    "
                    INSERT INTO tracking_bahan (kode_bahan, lokasi, created_at, updated_at)
                    VALUES (?, 'gudang', NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        lokasi = VALUES(lokasi),
                        updated_at = NOW()
                    ",
                    [$kodeBahan],
                );

                return $barcode;
            });

            return response()->json([
                'success' => true,
                'message' => 'Barcode berhasil disimpan',
                'data' => $barcode,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating barcode: ' . $e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal membuat barcode: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Generate kode bahan dengan format A0001 - Z9999
     * A0001 -> A9999 -> B0001 -> B9999 -> ... -> Z9999
     */
    private function generateKodeBahan()
    {
        // 1. Cari latest di BahanMasuk (Urutkan Huruf DESC, lalu Angka DESC)
        $latestBahanMasuk = BahanMasuk::whereRaw("TRIM(kode_bahan) REGEXP '^[A-Z][0-9]+$'")
            ->orderByRaw("SUBSTRING(TRIM(kode_bahan), 1, 1) DESC, CAST(SUBSTRING(TRIM(kode_bahan), 2) AS UNSIGNED) DESC")
            ->first();

        // 2. Cari latest di BarcodeBahan (Urutkan Huruf DESC, lalu Angka DESC)
        $latestBarcode = BarcodeBahan::whereRaw("TRIM(barcode_code) REGEXP '^[A-Z][0-9]+$'")
            ->orderByRaw("SUBSTRING(TRIM(barcode_code), 1, 1) DESC, CAST(SUBSTRING(TRIM(barcode_code), 2) AS UNSIGNED) DESC")
            ->first();

        $lastCode = null;
        
        // Pilih yang abjadnya paling tinggi, jika abjad sama pilih yang angkanya paling besar
        $codeBM = $latestBahanMasuk ? trim($latestBahanMasuk->kode_bahan) : '';
        $codeBC = $latestBarcode ? trim($latestBarcode->barcode_code) : '';

        if ($codeBM && $codeBC) {
            $letterBM = substr($codeBM, 0, 1);
            $letterBC = substr($codeBC, 0, 1);
            $numBM = (int)substr($codeBM, 1);
            $numBC = (int)substr($codeBC, 1);

            if ($letterBM > $letterBC || ($letterBM === $letterBC && $numBM >= $numBC)) {
                $lastCode = $codeBM;
            } else {
                $lastCode = $codeBC;
            }
        } else {
            $lastCode = $codeBM ?: $codeBC;
        }

        if (!$lastCode) {
            return 'A0001';
        }

        // Extract letter and number
        preg_match('/^([A-Z])(\d+)$/', $lastCode, $matches);

        if (!$matches) {
            return 'A0001';
        }

        $letter = $matches[1];
        $numberString = $matches[2];
        $number = (int) $numberString;

        // Increment number
        $number++;

        // Keep original padding length (C226 -> C227)
        $newCode = $letter . str_pad($number, strlen($numberString), '0', STR_PAD_LEFT);

        // Move to next letter if overflow
        $maxVal = (int)str_repeat('9', strlen($numberString));
        if ($number > $maxVal) {
            $number = 1;
            $letter = chr(ord($letter) + 1);

            if ($letter > 'Z') {
                throw new \Exception('Kode bahan sudah mencapai batas maksimum.');
            }
            $newCode = $letter . str_pad($number, strlen($numberString), '0', STR_PAD_LEFT);
        }

        // Safety check unique
        $maxRetries = 10;
        $retryCount = 0;
        while (BarcodeBahan::where('barcode_code', $newCode)->exists() && $retryCount < $maxRetries) {
            $number++;
            if ($number > $maxVal) {
                $number = 1;
                $letter = chr(ord($letter) + 1);
            }
            $newCode = $letter . str_pad($number, strlen($numberString), '0', STR_PAD_LEFT);
            $retryCount++;
        }

        return $newCode;
    }

    public function scan()
    {
        $suppliers = Supplier::orderBy('nama', 'asc')->get(['id', 'nama']);
        $masterBahan = \App\Models\MasterBahan::orderBy('nama_bahan', 'asc')->get(['id', 'nama_bahan']);
        $suratJalanMasuk = \App\Models\SuratJalanMasuk::orderByDesc('tanggal')->get(['id', 'no_surat_jalan']);
        $kepemilikans = \App\Models\MasterKepemilikan::orderBy('nama_kepemilikan', 'asc')->get(['id', 'nama_kepemilikan']);

        return Inertia::render('Barcode/Scan', [
            'suppliers' => $suppliers,
            'masterBahan' => $masterBahan,
            'suratJalanMasuk' => $suratJalanMasuk,
            'kepemilikans' => $kepemilikans,
        ]);
    }

    public function list(Request $request)
    {
        $query = BarcodeBahan::with('kepemilikan');

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
            $query->where(function ($q) use ($search) {
                $q->where('barcode_code', 'like', "%{$search}%")
                    ->orWhere('kode_bahan', 'like', "%{$search}%")
                    ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        $barcodes = $query->orderBy('created_at', 'desc')->paginate(20)->through(
            fn($item) => [
                'id' => $item->id,
                'barcode_code' => $item->barcode_code,
                'kode_bahan' => $item->kode_bahan,
                'nama_bahan' => $item->nama_bahan,
                'supplier' => $item->supplier,
                'quantity' => $item->quantity,
                'satuan' => $item->satuan,
                'rp_per_yard' => $item->rp_per_yard,
                'harga_keluar' => $item->harga_keluar,
                'total_harga' => $item->total_harga,
                'no_surat_jalan' => $item->no_surat_jalan,
                'tanggal' => $item->tanggal,
                'harga_sudah_diisi' => $item->harga_sudah_diisi,
                'kepemilikan' => $item->kepemilikan
                    ? [
                        'id' => $item->kepemilikan->id,
                        'nama_kepemilikan' => $item->kepemilikan->nama_kepemilikan,
                    ]
                    : null,
                'created_at' => $item->created_at,
            ],
        );

        // Get unique suppliers for filter (exclude null/empty)
        $suppliers = BarcodeBahan::select('supplier')->whereNotNull('supplier')->where('supplier', '!=', '')->distinct()->orderBy('supplier', 'asc')->pluck('supplier');

        // Statistics
        $stats = [
            'total' => BarcodeBahan::count('*'),
            'sudah_harga' => BarcodeBahan::where('harga_sudah_diisi', '=', true, 'and')->count('*'),
            'belum_harga' => BarcodeBahan::where('harga_sudah_diisi', '=', false, 'and')->count('*'),
        ];

        return Inertia::render('Barcode/List', [
            'barcodes' => $barcodes,
            'suppliers' => $suppliers,
            'stats' => $stats,
            'filters' => $request->only(['status', 'supplier', 'search']),
        ]);
    }

    public function findByCode(Request $request)
    {
        $request->validate([
            'barcode_code' => 'required|string',
        ]);

        $barcode = BarcodeBahan::where('barcode_code', '=', $request->barcode_code, 'and')->first();

        if (!$barcode) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Barcode tidak ditemukan',
                ],
                404,
            );
        }

        return response()->json([
            'success' => true,
            'data' => $barcode,
        ]);
    }

    public function updatePrice(Request $request, $id)
    {
        $validated = $request->validate([
            'rp_per_yard' => 'required|numeric|min:0',
        ]);

        $barcode = BarcodeBahan::findOrFail($id);

        $barcode->rp_per_yard = $validated['rp_per_yard'];
        $barcode->total_harga = $barcode->quantity * $validated['rp_per_yard'];
        $barcode->harga_sudah_diisi = true;
        $barcode->save();

        return response()->json([
            'success' => true,
            'message' => 'Harga berhasil diupdate',
            'data' => $barcode,
        ]);
    }

    private function findMasterId(string $table, string $column, ?string $value): ?int
    {
        if (!$value) return null;
        
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        $record = DB::table($table)->whereRaw("LOWER(REPLACE(REPLACE(REPLACE($column, '-', ''), '.', ''), ' ', '')) = ?", [str_replace(' ', '', $normalized)])->first();
        
        return $record ? $record->id : null;
    }

    public function updateComplete(Request $request, $id)
    {
        $validated = $request->validate([
            'supplier' => 'required|string',
            'nama_bahan' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'satuan' => 'required|in:yard,meter,kg',
            'rp_per_yard' => 'required|numeric|min:0',
            'harga_keluar' => 'required|numeric|min:0',
            'no_surat_jalan' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'kepemilikan_id' => 'required|exists:master_kepemilikans,id',
            'master_bahan_id' => 'nullable|exists:master_bahan,id',
        ]);

        $barcode = BarcodeBahan::findOrFail($id);

        try {
            DB::beginTransaction();

            // Resolve IDs if not provided but names are present
            $masterBahanId = $validated['master_bahan_id'] ?? $this->findMasterId('master_bahan', 'nama_bahan', $validated['nama_bahan']);
            $masterKepemilikanId = $validated['kepemilikan_id'] ?? $this->findMasterId('master_kepemilikans', 'nama_kepemilikan', $validated['supplier']);

            // Cek apakah sudah pernah diisi sebelumnya (untuk membedakan "Input Pertama" vs "Edit")
            $sudahDiisiSebelumnya = $barcode->harga_sudah_diisi;
            $qtyLama = (float) $barcode->quantity;
            $kodeBahanLama = $barcode->kode_bahan;

            // Update barcode record
            $barcode->supplier = $validated['supplier'];
            $barcode->nama_bahan = $validated['nama_bahan'];
            $barcode->quantity = $validated['quantity'];
            $barcode->satuan = $validated['satuan'];
            $barcode->rp_per_yard = $validated['rp_per_yard'];
            $barcode->harga_keluar = $validated['harga_keluar'];
            $barcode->no_surat_jalan = $validated['no_surat_jalan'];
            $barcode->tanggal = $validated['tanggal_masuk'];
            $barcode->kepemilikan_id = $masterKepemilikanId;
            $barcode->total_harga = $validated['quantity'] * $validated['rp_per_yard'];
            $barcode->harga_sudah_diisi = true;
            $barcode->save();

            // 1. Sinkronisasi ke tabel bahan_masuk (barang_masuk)
            if ($sudahDiisiSebelumnya) {
                // Jika EDIT: Update record bahan_masuk yang berhubungan (berdasarkan kode_bahan lama dan no_surat_jalan lama jika ada)
                // Kita coba cari yang paling mendekati (biasanya yang id-nya paling besar atau sesuai kode)
                $bahanMasuk = BahanMasuk::where('kode_bahan', $kodeBahanLama)
                    ->where('no_surat_jalan', $barcode->getOriginal('no_surat_jalan'))
                    ->orderBy('id', 'desc')
                    ->first();

                if ($bahanMasuk) {
                    $bahanMasuk->update([
                        'tanggal' => $validated['tanggal_masuk'],
                        'no_surat_jalan' => $validated['no_surat_jalan'],
                        'supplier' => $validated['supplier'],
                        'kode_bahan' => $barcode->kode_bahan,
                        'nama_bahan' => $validated['nama_bahan'],
                        'master_bahan_id' => $masterBahanId,
                        'master_kepemilikan_id' => $masterKepemilikanId,
                        'quantity' => $validated['quantity'],
                        'satuan' => $validated['satuan'],
                        'harga_satuan' => $validated['rp_per_yard'],
                        'total' => $validated['quantity'] * $validated['rp_per_yard'],
                    ]);
                }
            } else {
                // Jika INPUT BARU: Create entri baru
                $noNota = $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');
                BahanMasuk::create([
                    'tanggal' => $validated['tanggal_masuk'],
                    'no_surat_jalan' => $validated['no_surat_jalan'],
                    'no_nota' => $noNota,
                    'supplier' => $validated['supplier'],
                    'kode_bahan' => $barcode->kode_bahan,
                    'nama_bahan' => $validated['nama_bahan'],
                    'master_bahan_id' => $masterBahanId,
                    'master_kepemilikan_id' => $masterKepemilikanId,
                    'quantity' => $validated['quantity'],
                    'satuan' => $validated['satuan'],
                    'harga_satuan' => $validated['rp_per_yard'],
                    'total' => $validated['quantity'] * $validated['rp_per_yard'],
                ]);
            }

            // 2. Update stok bahan
            $kodeBahan = $barcode->kode_bahan;
            $qty = (float) $validated['quantity'];

            if ($sudahDiisiSebelumnya) {
                // Jika EDIT: Hitung selisih
                if ($kodeBahanLama === $kodeBahan) {
                    $selisih = $qty - $qtyLama;
                    if ($selisih != 0) {
                        DB::statement("UPDATE stok_bahan SET quantity = GREATEST(0, quantity + ?), updated_at = NOW() WHERE kode_bahan = ?", [$selisih, $kodeBahan]);
                    }
                } else {
                    // Jika KODE BERUBAH: Kurangi stok kode lama, tambah stok kode baru
                    DB::statement("UPDATE stok_bahan SET quantity = GREATEST(0, quantity - ?), updated_at = NOW() WHERE kode_bahan = ?", [$qtyLama, $kodeBahanLama]);
                    DB::statement("INSERT INTO stok_bahan (kode_bahan, quantity, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + ?, updated_at = NOW()", [$kodeBahan, $qty, $qty]);
                }
            } else {
                // Jika INPUT BARU: Tambah stok
                DB::statement("INSERT INTO stok_bahan (kode_bahan, quantity, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + ?, updated_at = NOW()", [$kodeBahan, $qty, $qty]);
            }

            // 3. Update tracking lokasi
            DB::statement(
                "INSERT INTO tracking_bahan (kode_bahan, lokasi, created_at, updated_at)
                VALUES (?, 'gudang', NOW(), NOW())
                ON DUPLICATE KEY UPDATE lokasi = VALUES(lokasi), updated_at = NOW()",
                [$barcode->barcode_code]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $sudahDiisiSebelumnya ? 'Data berhasil diperbarui' : 'Data lengkap berhasil disimpan',
                'data' => $barcode,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing/editing barcode data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $barcode = BarcodeBahan::findOrFail($id);

        try {
            DB::beginTransaction();

            // 1. Kurangi stok jika data sudah lengkap
            if ($barcode->harga_sudah_diisi) {
                DB::statement("UPDATE stok_bahan SET quantity = GREATEST(0, quantity - ?), updated_at = NOW() WHERE kode_bahan = ?", [(float)$barcode->quantity, $barcode->kode_bahan]);
                
                // 2. Hapus dari bahan_masuk (log transaksi masuk)
                BahanMasuk::where('kode_bahan', $barcode->kode_bahan)
                    ->where('no_surat_jalan', $barcode->no_surat_jalan)
                    ->delete();
            }

            // 3. Hapus tracking
            DB::table('tracking_bahan')->where('kode_bahan', $barcode->barcode_code)->delete();

            // 4. Hapus barcode
            $barcode->delete();

            DB::commit();

            return redirect()->back()->with('message', 'Barcode berhasil dihapus dan stok diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barcode: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus barcode: ' . $e->getMessage());
        }
    }
}
