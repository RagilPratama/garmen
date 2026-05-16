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

        return BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->when($search, fn($q) => $q->where('kode_bahan', 'like', "%{$search}%"))
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', $namaBahan))
            ->when($supplier, fn($q) => $q->where('supplier', $supplier))
            ->orderBy('created_at', 'desc');
    }

    public function index()
    {
        $query = $this->getFilteredQuery()
            ->paginate(20)
            ->appends(request()->query());

        // Daftar nama bahan unik untuk filter (hanya yang masih di gudang)
        $namaBahanOptions = BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->whereNotNull('nama_bahan')
            ->select('nama_bahan')
            ->distinct()
            ->orderBy('nama_bahan')
            ->pluck('nama_bahan');

        // Daftar supplier unik untuk filter
        $supplierOptions = BarcodeBahan::where('harga_sudah_diisi', true)
            ->whereDoesntHave('suratJalanGarmenItem')
            ->whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

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
