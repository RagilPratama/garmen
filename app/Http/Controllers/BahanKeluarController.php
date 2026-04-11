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
        $search  = request('search');
        $page    = max(1, (int) request('page', 1));
        $perPage = 15;

        $rows = BahanKeluar::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('kode_bahan',     'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan', 'ilike', "%{$search}%")
            ))
            ->get();

        $grouped = $rows
            ->groupBy(fn($r) => $r->no_surat_jalan ?: 'id_'.$r->id)
            ->map(fn($items) => [
                'id'             => $items->first()->no_surat_jalan ?? (string) $items->first()->id,
                'no_surat_jalan' => $items->first()->no_surat_jalan,
                'tanggal'        => $items->first()->tanggal,
                'grand_total'    => $items->sum('total'),
                'items_count'    => $items->count(),
                'items'          => $items->values()->map(fn($i) => [
                    'id'          => $i->id,
                    'kode_bahan'  => $i->kode_bahan,
                    'yard'        => $i->yard,
                    'rp_per_yard' => $i->rp_per_yard,
                    'total'       => $i->total,
                ])->toArray(),
            ])
            ->values();

        $pageItems = $grouped->forPage($page, $perPage)->values();

        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            $grouped->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stok = StokBahan::orderBy('kode_bahan')->where('sisa_stok', '>', 0)->get(['kode_bahan', 'sisa_stok']);

        return Inertia::render('BahanKeluar/Index', [
            'data'           => $data,
            'stok'           => $stok,
            'nextSuratJalan' => $this->nextSuratJalan(BahanKeluar::class, 'BK-'),
        ]);
    }

    public function create()
    {
        return Inertia::render('BahanKeluar/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'no_surat_jalan'       => 'nullable|string|max:100',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.yard'         => 'required|numeric|min:0.01',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        $noSJ = $validated['no_surat_jalan'] ?: $this->nextSuratJalan(BahanKeluar::class, 'BK-');

        foreach ($validated['items'] as $item) {
            $this->validateStok($item['kode_bahan'], $item['yard']);
        }

        $stokDeltas = [];
        foreach ($validated['items'] as $item) {
            BahanKeluar::create([
                'tanggal'        => $validated['tanggal'],
                'no_surat_jalan' => $noSJ,
                'kode_bahan'     => $item['kode_bahan'],
                'yard'           => $item['yard'],
                'rp_per_yard'    => $item['rp_per_yard'],
                'total'          => $item['yard'] * $item['rp_per_yard'],
            ]);
            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['yard'];
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
            'tanggal'              => 'required|date',
            'no_surat_jalan'       => 'nullable|string|max:100',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.yard'         => 'required|numeric|min:0.01',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        $existing = BahanKeluar::where('no_surat_jalan', $bahanKeluar)->get();
        if ($existing->isEmpty() && is_numeric($bahanKeluar)) {
            $row = BahanKeluar::find($bahanKeluar);
            if ($row) {
                $existing = $row->no_surat_jalan
                    ? BahanKeluar::where('no_surat_jalan', $row->no_surat_jalan)->get()
                    : collect([$row]);
            }
        }

        // Reverse old stok deductions
        $stokDeltas = [];
        foreach ($existing as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->yard;
        }
        BahanKeluar::whereIn('id', $existing->pluck('id'))->delete();

        // Validate and create new items
        foreach ($validated['items'] as $item) {
            $alreadyRestored = $stokDeltas[$item['kode_bahan']] ?? 0;
            $effectiveYard   = $item['yard'] + $alreadyRestored; // net additional need
            if ($effectiveYard > 0) {
                $this->validateStok($item['kode_bahan'], $effectiveYard);
            }
        }

        $noSJ = $validated['no_surat_jalan'] ?: $bahanKeluar;
        foreach ($validated['items'] as $item) {
            BahanKeluar::create([
                'tanggal'        => $validated['tanggal'],
                'no_surat_jalan' => $noSJ,
                'kode_bahan'     => $item['kode_bahan'],
                'yard'           => $item['yard'],
                'rp_per_yard'    => $item['rp_per_yard'],
                'total'          => $item['yard'] * $item['rp_per_yard'],
            ]);
            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['yard'];
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
                $items = $item->no_surat_jalan
                    ? BahanKeluar::where('no_surat_jalan', $item->no_surat_jalan)->get()
                    : collect([$item]);
            }
        }

        $stokDeltas = [];
        foreach ($items as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->yard;
        }
        BahanKeluar::whereIn('id', $items->pluck('id'))->delete();
        $this->bulkKurangiStok($stokDeltas);

        return redirect()->route('bahan-keluar.index')->with('message', 'Data berhasil dihapus.');
    }

    private function validateStok(string $kodeBahan, float $yard): void
    {
        $stok = StokBahan::where('kode_bahan', $kodeBahan)->first();
        $sisa = (float) ($stok?->sisa_stok ?? 0);
        if ($yard > $sisa) {
            throw ValidationException::withMessages([
                'items' => "Stok tidak cukup untuk {$kodeBahan}. Sisa: {$sisa} yard.",
            ]);
        }
    }

    private function bulkKurangiStok(array $deltas): void
    {
        foreach ($deltas as $kode => $delta) {
            if ($delta == 0) continue;
            DB::statement("
                UPDATE stok_bahan
                SET total_keluar = GREATEST(0, total_keluar + ?),
                    sisa_stok    = GREATEST(0, total_masuk - GREATEST(0, total_keluar + ?)),
                    updated_at   = NOW()
                WHERE kode_bahan = ?
            ", [$delta, $delta, $kode]);
        }
    }
}

