<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\BahanMasukPembayaran;
use App\Models\Rekening;
use App\Models\Supplier;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class BahanMasukController extends Controller
{
    use GeneratesSuratJalan;
    public function index()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');
        $status = request('status');

        // Bahan masuk = SEMUA barcode yang sudah lengkap (history tracking)
        $query = \App\Models\BarcodeBahan::where('harga_sudah_diisi', '=', true, 'and')
            ->when(
                $search,
                fn($q) => $q->where(
                    fn($q) => $q
                        ->where('kode_bahan', 'like', "%{$search}%")
                        ->orWhere('nama_bahan', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%")
                        ->orWhere('no_surat_jalan', 'like', "%{$search}%"),
                ),
            )
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', '=', $namaBahan, 'and'))
            ->when($supplier, fn($q) => $q->where('supplier', '=', $supplier, 'and'))
            ->when($status === 'gudang', fn($q) => $q->whereDoesntHave('suratJalanGarmenItem'))
            ->when($status === 'garmen', fn($q) => $q->whereHas('suratJalanGarmenItem'))
            ->with('suratJalanGarmenItem.suratJalan')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        // Transform: tambahkan status lokasi
        $query->getCollection()->transform(function ($item) {
            $item->lokasi = $item->suratJalanGarmenItem ? 'Garmen' : 'Gudang';
            $item->no_sj_garmen = $item->suratJalanGarmenItem?->suratJalan?->no_surat_jalan ?? null;
            return $item;
        });

        // Daftar supplier unik untuk filter
        $supplierOptions = \App\Models\BarcodeBahan::where('harga_sudah_diisi', '=', true, 'and')
            ->whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier', 'asc')
            ->pluck('supplier');

        // Daftar nama bahan unik untuk filter
        $namaBahanOptions = \App\Models\BarcodeBahan::where('harga_sudah_diisi', '=', true, 'and')->whereNotNull('nama_bahan')->select('nama_bahan')->distinct()->orderBy('nama_bahan', 'asc')->pluck('nama_bahan');

        return Inertia::render('BahanMasuk/Index', [
            'data' => $query,
            'supplierOptions' => $supplierOptions,
            'namaBahanOptions' => $namaBahanOptions,
            'filters' => [
                'search' => $search,
                'nama_bahan' => $namaBahan,
                'supplier' => $supplier,
                'status' => $status,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('BahanMasuk/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'supplier' => 'required|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.kode_bahan' => 'required|string|max:100',
            'items.*.nama_bahan' => 'nullable|string|max:200',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.satuan' => 'required|in:yard,meter,kg',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $noNota = $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');
        $stokDeltas = [];

        foreach ($validated['items'] as $item) {
            BahanMasuk::create([
                'tanggal' => $validated['tanggal'],
                'no_surat_jalan' => $validated['no_surat_jalan'],
                'no_nota' => $noNota,
                'supplier' => $validated['supplier'],
                'kode_bahan' => $item['kode_bahan'],
                'nama_bahan' => $item['nama_bahan'] ?? null,
                'quantity' => $item['quantity'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $item['harga_satuan'],
                'total_harga' => $item['quantity'] * $item['harga_satuan'],
            ]);

            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['quantity'];
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
            'tanggal' => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'no_nota' => 'nullable|string|max:100',
            'supplier' => 'required|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.kode_bahan' => 'required|string|max:100',
            'items.*.nama_bahan' => 'nullable|string|max:200',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.satuan' => 'required|in:yard,meter,kg',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $existing = BahanMasuk::where('no_nota', '=', $bahanMasuk, 'and')->get();
        if ($existing->isEmpty() && is_numeric($bahanMasuk)) {
            $row = BahanMasuk::find($bahanMasuk, ['*']);
            if ($row) {
                $existing = $row->no_nota ? BahanMasuk::where('no_nota', '=', $row->no_nota, 'and')->get() : collect([$row]);
            }
        }

        // Reverse stok for all old items
        $stokDeltas = [];
        foreach ($existing as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->quantity;
        }
        BahanMasuk::whereIn('id', $existing->pluck('id'), 'and', false)->delete();

        // Re-insert updated items and apply stok
        $noNota = $validated['no_nota'] ?: $bahanMasuk;
        foreach ($validated['items'] as $item) {
            BahanMasuk::create([
                'tanggal' => $validated['tanggal'],
                'no_surat_jalan' => $validated['no_surat_jalan'],
                'no_nota' => $noNota,
                'supplier' => $validated['supplier'],
                'kode_bahan' => $item['kode_bahan'],
                'nama_bahan' => $item['nama_bahan'] ?? null,
                'quantity' => $item['quantity'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $item['harga_satuan'],
                'total_harga' => $item['quantity'] * $item['harga_satuan'],
            ]);

            $stokDeltas[$item['kode_bahan']] = ($stokDeltas[$item['kode_bahan']] ?? 0) + $item['quantity'];
        }

        $this->bulkAddStok($stokDeltas);

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy($bahanMasuk)
    {
        // $bahanMasuk is a no_nota string (from grouped rows)
        $items = BahanMasuk::where('no_nota', '=', $bahanMasuk, 'and')->get();

        // Fallback: if no_nota not found, try by ID
        if ($items->isEmpty() && is_numeric($bahanMasuk)) {
            $item = BahanMasuk::find($bahanMasuk, ['*']);
            if ($item) {
                $items = $item->no_nota ? BahanMasuk::where('no_nota', '=', $item->no_nota, 'and')->get() : collect([$item]);
            }
        }

        $stokDeltas = [];
        foreach ($items as $item) {
            $stokDeltas[$item->kode_bahan] = ($stokDeltas[$item->kode_bahan] ?? 0) - $item->quantity;
        }
        BahanMasuk::whereIn('id', $items->pluck('id'), 'and', false)->delete();
        $this->bulkAddStok($stokDeltas);

        return redirect()->route('bahan-masuk.index')->with('message', 'Data berhasil dihapus.');
    }

    private function getFilteredQuery()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');
        $status = request('status');

        return \App\Models\BarcodeBahan::where('harga_sudah_diisi', '=', true, 'and')
            ->when(
                $search,
                fn($q) => $q->where(
                    fn($q) => $q
                        ->where('kode_bahan', 'like', "%{$search}%")
                        ->orWhere('nama_bahan', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%")
                        ->orWhere('no_surat_jalan', 'like', "%{$search}%"),
                ),
            )
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', '=', $namaBahan, 'and'))
            ->when($supplier, fn($q) => $q->where('supplier', '=', $supplier, 'and'))
            ->when($status === 'gudang', fn($q) => $q->whereDoesntHave('suratJalanGarmenItem'))
            ->when($status === 'garmen', fn($q) => $q->whereHas('suratJalanGarmenItem'))
            ->with('suratJalanGarmenItem.suratJalan')
            ->orderBy('created_at', 'desc');
    }

    public function exportExcel()
    {
        $data = $this->getFilteredQuery()->get();

        $data->transform(function ($item) {
            $item->lokasi = $item->suratJalanGarmenItem ? 'Garmen' : 'Gudang';
            return $item;
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bahan Masuk');

        $headers = ['No', 'Tanggal', 'No. Surat Jalan', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty', 'Satuan', 'Harga/Yard', 'Total Harga', 'Lokasi'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        $row = 2;
        foreach ($data as $i => $item) {
            $sheet->fromArray(
                [
                    $i + 1,
                    $item->tanggal ? $item->tanggal->format('d/m/Y') : '-',
                    $item->no_surat_jalan,
                    $item->kode_bahan,
                    $item->nama_bahan,
                    $item->supplier,
                    (float) $item->quantity,
                    $item->satuan ?? 'yard',
                    (float) $item->rp_per_yard,
                    (float) $item->total_harga,
                    $item->lokasi,
                ],
                null,
                "A{$row}",
            );
            $row++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'bahan_masuk_' . date('Ymd_His') . '.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf()
    {
        $data = $this->getFilteredQuery()->get();

        $data->transform(function ($item) {
            $item->lokasi = $item->suratJalanGarmenItem ? 'Garmen' : 'Gudang';
            return $item;
        });

        $pdf = Pdf::loadView('exports.bahan-masuk', [
            'data' => $data,
            'title' => 'Laporan Bahan Masuk',
            'tanggal' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('bahan_masuk_' . date('Ymd_His') . '.pdf');
    }

    private function bulkAddStok(array $deltas): void
    {
        if (empty($deltas)) {
            return;
        }

        foreach ($deltas as $kode => $delta) {
            DB::statement(
                "
                INSERT INTO stok_bahan (kode_bahan, quantity, created_at, updated_at)
                VALUES (?, GREATEST(0, ?), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    quantity   = GREATEST(0, quantity + ?),
                    updated_at = NOW()
                ",
                [$kode, (float) $delta, (float) $delta],
            );
        }
    }
}
