<?php

namespace App\Http\Controllers;

use App\Models\SuratJalanGarmen;
use App\Models\SuratJalanGarmenItem;
use App\Models\BarcodeBahan;
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
            return response()->json($suratJalan);
        }

        return Inertia::render('SuratJalanGarmen/Show', [
            'suratJalan' => $suratJalan,
        ]);
    }

    public function destroy($id)
    {
        $suratJalan = SuratJalanGarmen::with('items')->findOrFail($id);

        DB::transaction(function () use ($suratJalan) {
            // Kembalikan stok
            foreach ($suratJalan->items as $item) {
                StokBahan::where('kode_bahan', $item->kode_bahan)->update([
                    'quantity' => DB::raw("quantity + {$item->quantity}"),
                ]);
            }

            $suratJalan->delete();
        });

        return redirect()->route('surat-jalan-garmen.index')->with('message', 'Surat jalan berhasil dihapus.');
    }

    /**
     * Scan barcode dan langsung tambahkan ke surat jalan (real-time)
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode_code' => 'required|string',
            'surat_jalan_id' => 'nullable|integer',
            'harga_keluar' => 'nullable|numeric|min:0',
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

        // Jika ada surat_jalan_id, langsung simpan ke database
        if ($request->surat_jalan_id) {
            $suratJalan = SuratJalanGarmen::findOrFail($request->surat_jalan_id);
            $hargaKeluar = (float) ($request->harga_keluar ?? $barcode->rp_per_yard);

            $item = DB::transaction(function () use ($suratJalan, $barcode, $hargaKeluar) {
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

        // Return data barcode untuk preview (termasuk harga masuk sebagai default)
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
