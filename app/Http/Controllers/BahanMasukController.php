<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\StokBahan;
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
                ->where('supplier',       'ilike', "%{$search}%")
                ->orWhere('kode_bahan',   'ilike', "%{$search}%")
                ->orWhere('no_nota',      'ilike', "%{$search}%")
                ->orWhere('no_surat_jalan','ilike',"%{$search}%")
                ->orWhere('status',       'ilike', "%{$search}%")
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
                'status'         => $items->first()->status,
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

        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped->forPage($page, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('BahanMasuk/Index', [
            'data'            => $data,
            'supplierOptions' => Supplier::orderBy('nama')->pluck('nama'),
            'nextSuratJalan'  => $this->nextSuratJalan(BahanMasuk::class, 'LJ-'),
            'nextNota'        => $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-'),
            'nextKodeBahan'   => $this->nextCode(BahanMasuk::class, 'kode_bahan', 'KB-'),
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
            'status'               => 'required|string|in:pending,diterima,ditolak',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.nama_bahan'   => 'nullable|string|max:200',
            'items.*.yard'         => 'required|numeric|min:0',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        $noNota = $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');

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
                'status'         => $validated['status'],
            ]);

            if ($validated['status'] === 'diterima') {
                $this->addStok($item['kode_bahan'], $item['yard']);
            }
        }

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
            'status'               => 'required|string|in:pending,diterima,ditolak',
            'items'                => 'required|array|min:1',
            'items.*.kode_bahan'   => 'required|string|max:100',
            'items.*.nama_bahan'   => 'nullable|string|max:200',
            'items.*.yard'         => 'required|numeric|min:0',
            'items.*.rp_per_yard'  => 'required|numeric|min:0',
        ]);

        // Find all existing rows for this nota (by no_nota or ID as fallback)
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
        foreach ($existing as $item) {
            if ($item->status === 'diterima') {
                $this->addStok($item->kode_bahan, -$item->yard);
            }
            $item->delete();
        }

        // Re-insert updated items
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
                'status'         => $validated['status'],
            ]);

            if ($validated['status'] === 'diterima') {
                $this->addStok($item['kode_bahan'], $item['yard']);
            }
        }

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

        foreach ($items as $item) {
            if ($item->status === 'diterima') {
                $this->addStok($item->kode_bahan, -$item->yard);
            }
            $item->delete();
        }

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil dihapus.');
    }

    private function addStok(string $kodeBahan, float $yard): void
    {
        $stok = StokBahan::firstOrCreate(
            ['kode_bahan' => $kodeBahan],
            ['total_masuk' => 0, 'total_keluar' => 0, 'sisa_stok' => 0]
        );
        $stok->total_masuk = max(0, $stok->total_masuk + $yard);
        $stok->sisa_stok   = max(0, $stok->total_masuk - $stok->total_keluar);
        $stok->save();
    }
}

