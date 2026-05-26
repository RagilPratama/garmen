<?php

namespace App\Http\Controllers;

use App\Models\BarcodeBahan;
use App\Models\SuratJalanMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuratJalanMasukController extends Controller
{
    public function index()
    {
        $search = request('search');

        $data = SuratJalanMasuk::when($search, fn($q) => $q->where('no_surat_jalan', 'like', "%{$search}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(request()->query());

        // Hitung jumlah barcode yang terhubung per surat jalan
        $data->getCollection()->transform(function ($item) {
            $barcodes = \App\Models\BarcodeBahan::where('no_surat_jalan', $item->no_surat_jalan)
                ->where('harga_sudah_diisi', true)
                ->get();
            $item->jumlah_item = $barcodes->count();
            $item->total_qty = (float) $barcodes->sum('quantity');
            $item->total_harga = (float) $barcodes->sum('total_harga');
            return $item;
        });

        return Inertia::render('SuratJalanMasuk/Index', [
            'data' => $data,
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function create()
    {
        if (auth()->user()?->role === 'admingarmen') {
            abort(403);
        }

        return Inertia::render('SuratJalanMasuk/Create', [
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role === 'admingarmen') {
            abort(403);
        }

        $request->validate([
            'no_surat_jalan' => 'required|string|max:100|unique:surat_jalan_masuk,no_surat_jalan',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        SuratJalanMasuk::create([
            'no_surat_jalan' => $request->no_surat_jalan,
            'no_nota' => $this->generateNextNota(),
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('surat-jalan-masuk.index')->with('message', 'Surat jalan masuk berhasil dibuat.');
    }

    public function destroy($id)
    {
        if (auth()->user()?->role === 'admingarmen') {
            abort(403);
        }

        $suratJalan = SuratJalanMasuk::findOrFail($id);
        $noSuratJalan = $suratJalan->no_surat_jalan;

        DB::transaction(function () use ($suratJalan, $noSuratJalan) {
            // Hanya barcode yang masih di gudang — stok sudah dikurangi saat dikirim ke garmen
            $barcodesGudang = BarcodeBahan::where('no_surat_jalan', $noSuratJalan)
                ->where('harga_sudah_diisi', true)
                ->whereDoesntHave('suratJalanGarmenItem')
                ->get();

            foreach ($barcodesGudang as $barcode) {
                DB::statement(
                    '
                    UPDATE stok_bahan
                    SET quantity = GREATEST(0, quantity - ?),
                        updated_at = NOW()
                    WHERE kode_bahan = ?
                ',
                    [(float) $barcode->quantity, $barcode->kode_bahan],
                );
            }

            // Reset barcode yang masih di gudang ke status belum lengkap
            BarcodeBahan::where('no_surat_jalan', $noSuratJalan)
                ->whereDoesntHave('suratJalanGarmenItem')
                ->update([
                    'no_surat_jalan' => null,
                    'supplier' => null,
                    'nama_bahan' => null,
                    'quantity' => null,
                    'satuan' => null,
                    'kepemilikan_id' => null,
                    'rp_per_yard' => null,
                    'harga_keluar' => 0,
                    'total_harga' => null,
                    'harga_sudah_diisi' => false,
                ]);

            // Barcode yang sudah dikirim ke garmen: hapus referensi SJ masuk saja
            BarcodeBahan::where('no_surat_jalan', $noSuratJalan)
                ->whereHas('suratJalanGarmenItem')
                ->update(['no_surat_jalan' => null]);

            $suratJalan->delete();
        });

        return redirect()->route('surat-jalan-masuk.index')->with('message', 'Surat jalan masuk berhasil dihapus, stok dan barcode terkait di-reset.');
    }

    public function detail()
    {
        $noSuratJalan = request('no_surat_jalan');

        $items = \App\Models\BarcodeBahan::where('no_surat_jalan', $noSuratJalan)
            ->where('harga_sudah_diisi', true)
            ->orderBy('kode_bahan')
            ->get();

        return response()->json($items);
    }

    private function generateNextNumber(): string
    {
        $latest = SuratJalanMasuk::orderByDesc('id')->first();

        if (!$latest) {
            return 'SJ-MSK-0001';
        }

        preg_match('/(\d+)$/', $latest->no_surat_jalan, $matches);
        $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;

        return 'SJ-MSK-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    private function generateNextNota(): string
    {
        $latest = SuratJalanMasuk::whereNotNull('no_nota')->orderByDesc('id')->first();

        if (!$latest || !$latest->no_nota) {
            return 'INV-SUP-0001';
        }

        preg_match('/(\d+)$/', $latest->no_nota, $matches);
        $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;

        return 'INV-SUP-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
