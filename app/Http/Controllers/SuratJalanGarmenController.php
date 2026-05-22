<?php

namespace App\Http\Controllers;

use App\Models\SuratJalanGarmen;
use App\Models\SuratJalanGarmenItem;
use App\Models\BarcodeBahan;
use App\Models\BahanKeluar;
use App\Models\BahanMasuk;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuratJalanGarmenController extends Controller
{
    public function index()
    {
        $search = request('search');
        $page = (int) request('page', 1);
        $perPage = 20;

        $data = SuratJalanGarmen::withCount('items')
            ->withSum('items', 'quantity')
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('no_surat_jalan', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%")
            ))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends(request()->query());

        return Inertia::render('SuratJalanGarmen/Index', [
            'data' => $data,
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function create()
    {
        return Inertia::render('SuratJalanGarmen/Create', [
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_jalan' => 'required|string|max:100|unique:surat_jalan_garmen,no_surat_jalan',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $suratJalan = SuratJalanGarmen::create([
            'no_surat_jalan' => $request->no_surat_jalan,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('surat-jalan-garmen.show', $suratJalan->id)->with('message', 'Surat jalan berhasil dibuat. Silakan scan barcode.');
    }

    public function show($id)
    {
        $suratJalan = SuratJalanGarmen::with('items')->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $suratJalan,
                'can_print' => $this->canPrintSuratJalan($suratJalan),
            ]);
        }

        return Inertia::render('SuratJalanGarmen/Show', [
            'suratJalan' => $suratJalan,
        ]);
    }

    public function updateApproval(Request $request, $id)
    {
        $request->validate([
            'marker_approved' => 'required|boolean',
            'pola_approved' => 'required|boolean',
            'superadmin_allow_print' => 'required|boolean',
        ]);

        $suratJalan = SuratJalanGarmen::findOrFail($id);

        if (!auth()->user()->isAdminGudang() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $suratJalan->marker_approved = $request->boolean('marker_approved');
        $suratJalan->pola_approved = $request->boolean('pola_approved');
        if (auth()->user()->isSuperAdmin()) {
            $suratJalan->superadmin_allow_print = $request->boolean('superadmin_allow_print');
        }
        $suratJalan->save();

        return response()->json([
            'success' => true,
            'data' => $suratJalan,
        ]);
    }

    private function canPrintSuratJalan(SuratJalanGarmen $suratJalan): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdminGudang()) {
            return $suratJalan->superadmin_allow_print || ($suratJalan->marker_approved && $suratJalan->pola_approved);
        }

        return false;
    }

    public function destroy($id)
    {
        $suratJalan = SuratJalanGarmen::with('items')->findOrFail($id);

        DB::transaction(function () use ($suratJalan) {
            $this->rollbackBahanKeluarForSuratJalan($suratJalan);
            $this->rollbackBahanMasukForSuratJalan($suratJalan);

            // Kembalikan stok ke gudang dan hapus item garmen
            foreach ($suratJalan->items as $item) {
                DB::statement(
                    "INSERT INTO stok_bahan (kode_bahan, quantity, created_at, updated_at)
                    VALUES (?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        quantity = quantity + VALUES(quantity),
                        updated_at = NOW()",
                    [$item->kode_bahan, $item->quantity]
                );

                $item->delete();
            }

            $suratJalan->delete();
        });

        return redirect()->route('surat-jalan-garmen.index')->with('message', 'Surat jalan berhasil dihapus.');
    }

    private function rollbackBahanMasukForSuratJalan(SuratJalanGarmen $suratJalan): void
    {
        $items = BahanMasuk::where('no_surat_jalan', $suratJalan->no_surat_jalan)->get();
        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            DB::statement(
                "UPDATE stok_bahan
                SET quantity = GREATEST(0, quantity - ?), updated_at = NOW()
                WHERE kode_bahan = ?",
                [$item->yard, $item->kode_bahan]
            );
        }

        BahanMasuk::whereIn('id', $items->pluck('id'))->delete();
    }

    private function rollbackBahanKeluarForSuratJalan(SuratJalanGarmen $suratJalan): void
    {
        $items = BahanKeluar::where('no_surat_jalan', $suratJalan->no_surat_jalan)->get();
        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            DB::statement(
                "INSERT INTO stok_bahan (kode_bahan, quantity, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    quantity = quantity + VALUES(quantity),
                    updated_at = NOW()",
                [$item->kode_bahan, $item->yard]
            );
        }

        BahanKeluar::whereIn('id', $items->pluck('id'))->delete();
    }

    /**
     * Scan barcode dan langsung tambahkan ke surat jalan (real-time)
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode_code' => 'required|string',
            'surat_jalan_id' => 'nullable|integer',
        ]);

        $barcode = BarcodeBahan::where('barcode_code', $request->barcode_code)
            ->where('harga_sudah_diisi', true)
            ->first();

        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak ditemukan atau data belum lengkap',
            ], 404);
        }

        // Cek apakah sudah ada di surat jalan lain
        $existing = SuratJalanGarmenItem::where('barcode_bahan_id', $barcode->id)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode ini sudah ada di surat jalan: ' . $existing->suratJalan->no_surat_jalan,
            ], 422);
        }

        // Jika ada surat_jalan_id, langsung simpan ke database (tanpa harga keluar)
        if ($request->surat_jalan_id) {
            $suratJalan = SuratJalanGarmen::findOrFail($request->surat_jalan_id);

            $item = DB::transaction(function () use ($suratJalan, $barcode) {
                $hargaKeluar = $barcode->harga_keluar ?? 0;

                $item = SuratJalanGarmenItem::create([
                    'surat_jalan_garmen_id' => $suratJalan->id,
                    'barcode_bahan_id' => $barcode->id,
                    'kode_bahan' => $barcode->kode_bahan,
                    'nama_bahan' => $barcode->nama_bahan,
                    'supplier' => $barcode->supplier,
                    'quantity' => $barcode->quantity,
                    'satuan' => $barcode->satuan ?? 'yard',
                    'harga_keluar' => $hargaKeluar,
                    'total_harga' => $barcode->quantity * $hargaKeluar,
                ]);

                // Kurangi stok gudang
                StokBahan::where('kode_bahan', $barcode->kode_bahan)->update([
                    'quantity' => DB::raw("GREATEST(0, quantity - {$barcode->quantity})"),
                ]);

                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => "{$barcode->kode_bahan} - {$barcode->nama_bahan} ditambahkan",
                'data' => $item,
            ]);
        }

        // Return data barcode untuk preview
        return response()->json([
            'success' => true,
            'data' => $barcode,
        ]);
    }

    /**
     * Hapus item dari surat jalan (kembalikan stok)
     */
    public function removeItem($id, $itemId)
    {
        $item = SuratJalanGarmenItem::where('surat_jalan_garmen_id', $id)->findOrFail($itemId);

        DB::transaction(function () use ($item) {
            // Kembalikan stok
            StokBahan::where('kode_bahan', $item->kode_bahan)->update([
                'quantity' => DB::raw("quantity + {$item->quantity}"),
            ]);

            $item->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus',
        ]);
    }

    /**
     * Update harga keluar item
     */
    public function updateItemHarga(Request $request, $id, $itemId)
    {
        $request->validate([
            'harga_keluar' => 'required|numeric|min:0',
        ]);

        $item = SuratJalanGarmenItem::where('surat_jalan_garmen_id', $id)->findOrFail($itemId);
        $item->harga_keluar = $request->harga_keluar;
        $item->total_harga = $item->quantity * $request->harga_keluar;
        $item->save();

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    private function generateNextNumber(): string
    {
        $latest = SuratJalanGarmen::orderByDesc('id')->first();

        if (!$latest) {
            return 'SJ-GRM-0001';
        }

        preg_match('/(\d+)$/', $latest->no_surat_jalan, $matches);
        $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;

        return 'SJ-GRM-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
