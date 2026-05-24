<?php

namespace App\Http\Controllers;

use App\Models\BahanKeluar;
use App\Models\StokBahan;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BahanKeluarController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $search = request('search');

        // Bahan keluar = surat jalan garmen (grouped by no surat jalan)
        $data = \App\Models\SuratJalanGarmen::withCount('items')
            ->withSum('items', 'quantity')
            ->when($search, fn($q) => $q->where('no_surat_jalan', 'like', "%{$search}%"))
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->appends(request()->query());

        return Inertia::render('BahanKeluar/Index', [
            'data' => $data,
        ]);
    }

    public function detail()
    {
        $noSuratJalan = request('no_surat_jalan');

        $suratJalan = \App\Models\SuratJalanGarmen::with('items')->where('no_surat_jalan', $noSuratJalan)->first();

        if (!$suratJalan) {
            return response()->json(['error' => 'Surat jalan tidak ditemukan'], 404);
        }

        return response()->json($suratJalan);
    }

    public function create()
    {
        return Inertia::render('BahanKeluar/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.kode_bahan' => 'required|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.satuan' => 'required|in:yard,meter,kg',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $noSJ = $validated['no_surat_jalan'] ?: $this->nextSuratJalan(BahanKeluar::class, 'BK-');

        foreach ($validated['items'] as $item) {
            $this->validateStok($item['kode_bahan'], $item['quantity']);
        }

        $stokDeltas = [];
        foreach ($validated['items'] as $item) {
            BahanKeluar::create([
                'tanggal' => $validated['tanggal'],
                'no_surat_jalan' => $noSJ,
                'kode_bahan' => $item['kode_bahan'],
                'quantity' => $item['quantity'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $item['harga_satuan'],
                'total_harga' => $item['quantity'] * $item['harga_satuan'],
            ]);
            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['quantity'];
        }

        $this->bulkKurangiStok($stokDeltas);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BahanKeluar $bahanKeluar)
    {
        return Inertia::render('BahanKeluar/Form', ['item' => $bahanKeluar]);
    }

    public function update(Request $request, $bahanKeluar)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.kode_bahan' => 'required|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.satuan' => 'required|in:yard,meter,kg',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $existing = BahanKeluar::where('no_surat_jalan', $bahanKeluar)->get();
        if ($existing->isEmpty() && is_numeric($bahanKeluar)) {
            $row = BahanKeluar::find($bahanKeluar);
            if ($row) {
                $existing = $row->no_surat_jalan ? BahanKeluar::where('no_surat_jalan', $row->no_surat_jalan)->get() : collect([$row]);
            }
        }

        // Reverse old stok deductions
        $stokDeltas = [];
        foreach ($existing as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->quantity;
        }
        BahanKeluar::whereIn('id', $existing->pluck('id'))->delete();

        // Validate and create new items
        foreach ($validated['items'] as $item) {
            $alreadyRestored = $stokDeltas[$item['kode_bahan']] ?? 0;
            $effectiveQuantity = $item['quantity'] + $alreadyRestored; // net additional need
            if ($effectiveQuantity > 0) {
                $this->validateStok($item['kode_bahan'], $effectiveQuantity);
            }
        }

        $noSJ = $validated['no_surat_jalan'] ?: $bahanKeluar;
        foreach ($validated['items'] as $item) {
            BahanKeluar::create([
                'tanggal' => $validated['tanggal'],
                'no_surat_jalan' => $noSJ,
                'kode_bahan' => $item['kode_bahan'],
                'quantity' => $item['quantity'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $item['harga_satuan'],
                'total_harga' => $item['quantity'] * $item['harga_satuan'],
            ]);
            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['quantity'];
        }

        $this->bulkKurangiStok($stokDeltas);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy($bahanKeluar)
    {
        $items = BahanKeluar::where('no_surat_jalan', $bahanKeluar)->get();
        if ($items->isEmpty() && is_numeric($bahanKeluar)) {
            $item = BahanKeluar::find($bahanKeluar);
            if ($item) {
                $items = $item->no_surat_jalan ? BahanKeluar::where('no_surat_jalan', $item->no_surat_jalan)->get() : collect([$item]);
            }
        }

        $stokDeltas = [];
        foreach ($items as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->quantity;
        }
        BahanKeluar::whereIn('id', $items->pluck('id'))->delete();
        $this->bulkKurangiStok($stokDeltas);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil dihapus.');
    }

    private function validateStok(string $kodeBahan, float $quantity): void
    {
        $stok = StokBahan::where('kode_bahan', $kodeBahan)->first();
        $sisa = (float) ($stok?->quantity ?? 0);
        if ($quantity > $sisa) {
            throw ValidationException::withMessages([
                'items' => "Stok tidak cukup untuk {$kodeBahan}. Sisa: {$sisa}.",
            ]);
        }
    }

    private function bulkKurangiStok(array $deltas): void
    {
        foreach ($deltas as $kode => $delta) {
            if ($delta == 0) {
                continue;
            }
            DB::statement(
                "
                UPDATE stok_bahan
                SET quantity   = GREATEST(0, quantity - ?),
                    updated_at = NOW()
                WHERE kode_bahan = ?
            ",
                [$delta, $kode],
            );
        }
    }
}
