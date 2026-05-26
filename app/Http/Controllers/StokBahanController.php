<?php

namespace App\Http\Controllers;

use App\Models\StokBahan;
use App\Models\BahanMasuk;
use App\Models\BarcodeBahan;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class StokBahanController extends Controller
{
    private function getFilteredQuery()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');

        $latestBahanMasukSub = BahanMasuk::query()
            ->selectRaw('MAX(id) as id, kode_bahan', [])
            ->groupBy('kode_bahan');

        return StokBahan::query()
            ->where('stok_bahan.quantity', '>', 0)
            ->leftJoin('tracking_bahan as tb', 'tb.kode_bahan', '=', 'stok_bahan.kode_bahan')
            ->leftJoinSub($latestBahanMasukSub, 'latest_bahan', function ($join) {
                $join->on('stok_bahan.kode_bahan', '=', 'latest_bahan.kode_bahan');
            })
            ->leftJoin('bahan_masuk as bm', 'bm.id', '=', 'latest_bahan.id')
            ->where(function ($q) {
                $q->whereNull('tb.lokasi')->orWhere('tb.lokasi', 'gudang');
            })
            ->when($search, fn($q) => $q->where('stok_bahan.kode_bahan', 'like', "%{$search}%"))
            ->when($namaBahan, fn($q) => $q->where('bm.nama_bahan', $namaBahan))
            ->when($supplier, fn($q) => $q->where('bm.supplier', $supplier))
            ->select([
                'stok_bahan.id',
                'stok_bahan.kode_bahan',
                'stok_bahan.quantity',
                'bm.no_surat_jalan',
                'bm.nama_bahan',
                'bm.supplier',
                'bm.satuan',
                'bm.harga_satuan as rp_per_yard',
                DB::raw('COALESCE(stok_bahan.quantity, 0) * COALESCE(bm.harga_satuan, 0) as total_harga'),
            ])
            ->orderBy('stok_bahan.kode_bahan');
    }

    public function index()
    {
        $query = $this->getFilteredQuery()
            ->paginate(20)
            ->appends(request()->query());

        $latestBahanMasukSub = BahanMasuk::query()
            ->selectRaw('MAX(id) as id, kode_bahan', [])
            ->groupBy('kode_bahan');

        // Daftar nama bahan unik untuk filter (hanya yang stoknya masih ada)
        $namaBahanOptions = DB::table('stok_bahan')
            ->where('stok_bahan.quantity', '>', 0)
            ->leftJoin('tracking_bahan as tb', 'tb.kode_bahan', '=', 'stok_bahan.kode_bahan')
            ->leftJoinSub($latestBahanMasukSub, 'latest_bahan', function ($join) {
                $join->on('stok_bahan.kode_bahan', '=', 'latest_bahan.kode_bahan');
            })
            ->leftJoin('bahan_masuk as bm', 'bm.id', '=', 'latest_bahan.id')
            ->where(function ($q) {
                $q->whereNull('tb.lokasi')->orWhere('tb.lokasi', 'gudang');
            })
            ->whereNotNull('bm.nama_bahan')
            ->select('bm.nama_bahan')
            ->distinct()
            ->orderBy('bm.nama_bahan')
            ->pluck('bm.nama_bahan');

        // Daftar supplier unik untuk filter (hanya yang stoknya masih ada)
        $supplierOptions = DB::table('stok_bahan')
            ->where('stok_bahan.quantity', '>', 0)
            ->leftJoin('tracking_bahan as tb', 'tb.kode_bahan', '=', 'stok_bahan.kode_bahan')
            ->leftJoinSub($latestBahanMasukSub, 'latest_bahan', function ($join) {
                $join->on('stok_bahan.kode_bahan', '=', 'latest_bahan.kode_bahan');
            })
            ->leftJoin('bahan_masuk as bm', 'bm.id', '=', 'latest_bahan.id')
            ->where(function ($q) {
                $q->whereNull('tb.lokasi')->orWhere('tb.lokasi', 'gudang');
            })
            ->whereNotNull('bm.supplier')
            ->where('bm.supplier', '!=', '')
            ->select('bm.supplier')
            ->distinct()
            ->orderBy('bm.supplier')
            ->pluck('bm.supplier');

        return Inertia::render('StokBahan/Index', [
            'data' => $query,
            'namaBahanOptions' => $namaBahanOptions,
            'supplierOptions' => $supplierOptions,
            'filters' => [
                'search' => request('search'),
                'nama_bahan' => request('nama_bahan'),
                'supplier' => request('supplier'),
            ],
        ]);
    }

    public function exportExcel()
    {
        $data = $this->getFilteredQuery()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stok Bahan Gudang');

        // Header
        $headers = ['No', 'No. Surat Jalan', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty', 'Satuan', 'Harga/Yard', 'Total Harga'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($data as $i => $item) {
            $sheet->fromArray([
                $i + 1,
                $item->no_surat_jalan,
                $item->kode_bahan,
                $item->nama_bahan,
                $item->supplier,
                (float) $item->quantity,
                $item->satuan ?? 'yard',
                (float) $item->rp_per_yard,
                (float) $item->total_harga,
            ], null, "A{$row}");
            $row++;
        }

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'stok_bahan_gudang_' . date('Ymd_His') . '.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf()
    {
        $data = $this->getFilteredQuery()->get();

        $pdf = Pdf::loadView('exports.stok-bahan-gudang', [
            'data' => $data,
            'title' => 'Laporan Stok Bahan Gudang',
            'tanggal' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('stok_bahan_gudang_' . date('Ymd_His') . '.pdf');
    }
}
