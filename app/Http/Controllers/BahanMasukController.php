<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\BahanMasukPembayaran;
use App\Models\Rekening;
use App\Models\Supplier;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BahanMasukController extends Controller
{
    use GeneratesSuratJalan;
    public function index()
    {
        $search  = request('search');
        $page    = max(1, (int) request('page', 1));
        $perPage = 15;

        $rows = BahanMasuk::latest()
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('supplier',        'like', "%{$search}%")
                ->orWhere('kode_bahan',    'like', "%{$search}%")
                ->orWhere('no_nota',       'like', "%{$search}%")
                ->orWhere('no_surat_jalan','like', "%{$search}%")
            ))
            ->get();

        $grouped = $rows
            ->groupBy(fn($r) => $r->no_nota ?: 'id_'.$r->id)
            ->map(fn($items) => [
                'id'             => $items->first()->no_nota ?? (string) $items->first()->id,
                'no_nota'        => $items->first()->no_nota,
                'tanggal'        => $items->first()->tanggal,
                'no_surat_jalan' => $items->first()->no_surat_jalan,
                'supplier'       => $items->first()->supplier,
                'grand_total'    => $items->sum('total'),
                'items_count'    => $items->count(),
                'items'          => $items->values()->map(fn($i) => [
                    'id'          => $i->id,
                    'kode_bahan'  => $i->kode_bahan,
                    'nama_bahan'  => $i->nama_bahan,
                    'yard'        => $i->yard,
                    'rp_per_yard' => $i->rp_per_yard,
                    'total'       => $i->total,
                ])->toArray(),
            ])
            ->values();

        $pageItems   = $grouped->forPage($page, $perPage)->values();
        $noNotaList  = $pageItems->pluck('no_nota')->filter()->values()->toArray();
        $payments    = BahanMasukPembayaran::whereIn('no_nota', $noNotaList)
            ->orderBy('tanggal_bayar')
            ->get()
            ->groupBy('no_nota');

        $pageItems = $pageItems->map(function ($nota) use ($payments) {
            $pays         = $payments->get($nota['no_nota'], collect());
            $totalDibayar = (float) $pays->sum('jumlah');
            return array_merge($nota, [
                'total_dibayar' => $totalDibayar,
                'sisa_tagihan'  => (float) $nota['grand_total'] - $totalDibayar,
                'pembayaran'    => $pays->map(fn($p) => [
                    'id'            => $p->id,
                    'tanggal_bayar' => $p->tanggal_bayar,
                    'jumlah'        => $p->jumlah,
                    'metode'        => $p->metode,
                    'rekening_id'   => $p->rekening_id,
                    'keterangan'    => $p->keterangan,
                ])->values()->toArray(),
            ]);
        });

        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            $grouped->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('BahanMasuk/Index', [
            'data'              => $data,
            'supplierOptions'   => Supplier::orderBy('nama')->pluck('nama'),
            'rekeningOptions'   => Rekening::orderBy('bank')->get(['id', 'bank', 'nama', 'nomor_rekening']),
            'nextSuratJalan'    => $this->nextSuratJalan(BahanMasuk::class, 'LJ-'),
            'nextNota'          => $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-'),
            'nextKodeBahan'     => $this->nextCode(BahanMasuk::class, 'kode_bahan', 'KB-'),
            'supplierBahanMap'  => BahanMasuk::select('supplier', 'kode_bahan', 'nama_bahan')
                ->whereNotNull('kode_bahan')
                ->distinct()
                ->orderBy('supplier')
                ->orderBy('kode_bahan')
                ->get()
                ->groupBy('supplier')
                ->map(fn($items) => $items->map(fn($i) => [
                    'kode_bahan' => $i->kode_bahan,
                    'nama_bahan' => $i->nama_bahan,
                ])->values())
                ->toArray(),
        ]);
    }

    public function create()
    {
        return Inertia::render('BahanMasuk/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'no_surat_jalan'       => 'nullable|string|max:100',
            'supplier'             => 'required|string|max:200',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.nama_bahan'   => 'nullable|string|max:200',
            'items.*.yard'         => 'required|numeric|min:0',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        $noNota     = $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');
        $stokDeltas = [];

        foreach ($validated['items'] as $item) {
            BahanMasuk::create([
                'tanggal'        => $validated['tanggal'],
                'no_surat_jalan' => $validated['no_surat_jalan'],
                'no_nota'        => $noNota,
                'supplier'       => $validated['supplier'],
                'kode_bahan'     => $item['kode_bahan'],
                'nama_bahan'     => $item['nama_bahan'] ?? null,
                'yard'           => $item['yard'],
                'rp_per_yard'    => $item['rp_per_yard'],
                'total'          => $item['yard'] * $item['rp_per_yard'],
            ]);

            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['yard'];
        }

        $this->bulkAddStok($stokDeltas);

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function edit(BahanMasuk $bahanMasuk)
    {
        return Inertia::render('BahanMasuk/Form', ['item' => $bahanMasuk]);
    }

    public function update(Request $request, $bahanMasuk)
    {
        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'no_surat_jalan'       => 'nullable|string|max:100',
            'no_nota'              => 'nullable|string|max:100',
            'supplier'             => 'required|string|max:200',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.nama_bahan'   => 'nullable|string|max:200',
            'items.*.yard'         => 'required|numeric|min:0',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        $existing = BahanMasuk::where('no_nota', $bahanMasuk)->get();
        if ($existing->isEmpty() && is_numeric($bahanMasuk)) {
            $row = BahanMasuk::find($bahanMasuk);
            if ($row) {
                $existing = $row->no_nota
                    ? BahanMasuk::where('no_nota', $row->no_nota)->get()
                    : collect([$row]);
            }
        }

        // Reverse stok for all old items
        $stokDeltas = [];
        foreach ($existing as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->yard;
        }
        BahanMasuk::whereIn('id', $existing->pluck('id'))->delete();

        // Re-insert updated items and apply stok
        $noNota = $validated['no_nota'] ?: $bahanMasuk;
        foreach ($validated['items'] as $item) {
            BahanMasuk::create([
                'tanggal'        => $validated['tanggal'],
                'no_surat_jalan' => $validated['no_surat_jalan'],
                'no_nota'        => $noNota,
                'supplier'       => $validated['supplier'],
                'kode_bahan'     => $item['kode_bahan'],
                'nama_bahan'     => $item['nama_bahan'] ?? null,
                'yard'           => $item['yard'],
                'rp_per_yard'    => $item['rp_per_yard'],
                'total'          => $item['yard'] * $item['rp_per_yard'],
            ]);

            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['yard'];
        }

        $this->bulkAddStok($stokDeltas);

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy($bahanMasuk)
    {
        // $bahanMasuk is a no_nota string (from grouped rows)
        $items = BahanMasuk::where('no_nota', $bahanMasuk)->get();

        // Fallback: if no_nota not found, try by ID
        if ($items->isEmpty() && is_numeric($bahanMasuk)) {
            $item = BahanMasuk::find($bahanMasuk);
            if ($item) {
                $items = $item->no_nota
                    ? BahanMasuk::where('no_nota', $item->no_nota)->get()
                    : collect([$item]);
            }
        }

        $stokDeltas = [];
        foreach ($items as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->yard;
        }
        BahanMasuk::whereIn('id', $items->pluck('id'))->delete();
        $this->bulkAddStok($stokDeltas);

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil dihapus.');
    }

    private function bulkAddStok(array $deltas): void
    {
        if (empty($deltas)) {
            return;
        }

        // MySQL: INSERT ... ON DUPLICATE KEY UPDATE
        $placeholders = [];
        $bindings     = [];

        foreach ($deltas as $kode => $delta) {
            $placeholders[] = '(?, ?, 0, GREATEST(0, ?))';
            $bindings[]     = $kode;
            $bindings[]     = (float) max(0, $delta);
            $bindings[]     = (float) max(0, $delta);
        }

        $values = implode(', ', $placeholders);

        \DB::statement("
            INSERT INTO stok_bahan (kode_bahan, total_masuk, total_keluar, sisa_stok)
            VALUES {$values}
            ON DUPLICATE KEY UPDATE
                total_masuk = GREATEST(0, total_masuk + VALUES(total_masuk)),
                sisa_stok   = GREATEST(0, GREATEST(0, total_masuk + VALUES(total_masuk)) - total_keluar),
                updated_at  = NOW()
        ", $bindings);
    }
}

