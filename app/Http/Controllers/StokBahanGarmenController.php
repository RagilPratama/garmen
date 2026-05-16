<?php

namespace App\Http\Controllers;

use App\Models\SuratJalanGarmenItem;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class StokBahanGarmenController extends Controller
{
    private function getFilteredQuery()
    {
        $search = request('search');
        $namaBahan = request('nama_bahan');
        $supplier = request('supplier');

        return SuratJalanGarmenItem::with('suratJalan')
            ->when($search, fn($q) => $q->where('kode_bahan', 'like', "%{$search}%"))
            ->when($namaBahan, fn($q) => $q->where('nama_bahan', $namaBahan))
            ->when($supplier, fn($q) => $q->where('supplier', $supplier))
            ->orderByDesc('created_at');
    }

    public function index()
    {
        $query = $this->getFilteredQuery()
            ->paginate(20)
            ->appends(request()->query());

        // Transform untuk tambahkan no_surat_jalan
        $query->getCollection()->transform(function ($item) {
            $item->no_surat_jalan = $item->suratJalan->no_surat_jalan ?? '—';
            $item->tanggal_kirim = $item->suratJalan->tanggal ?? null;
            return $item;
        });

        // Daftar nama bahan unik untuk filter
        $namaBahanOptions = SuratJalanGarmenItem::whereNotNull('nama_bahan')
            ->select('nama_bahan')
            ->distinct()
            ->orderBy('nama_bahan')
            ->pluck('nama_bahan');

        // Daftar supplier unik untuk filter
        $supplierOptions = SuratJalanGarmenItem::whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

        return Inertia::render('StokBahanGarmen/Index', [
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
        $sheet->setTitle('Stok Bahan Garmen');

        // Header
        $headers = ['No', 'No. Surat Jalan', 'Tanggal Kirim', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty', 'Satuan'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($data as $i => $item) {
            $sheet->fromArray([
                $i + 1,
                $item->suratJalan->no_surat_jalan ?? '—',
                $item->suratJalan->tanggal ? $item->suratJalan->tanggal->format('d/m/Y') : '—',
                $item->kode_bahan,
                $item->nama_bahan,
                $item->supplier,
                (float) $item->quantity,
                $item->satuan ?? 'yard',
            ], null, "A{$row}");
            $row++;
        }

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'stok_bahan_garmen_' . date('Ymd_His') . '.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf()
    {
        $data = $this->getFilteredQuery()->get();

        // Transform data for view
        $data->transform(function ($item) {
            $item->no_surat_jalan = $item->suratJalan->no_surat_jalan ?? '—';
            $item->tanggal_kirim = $item->suratJalan->tanggal ? $item->suratJalan->tanggal->format('d/m/Y') : '—';
            return $item;
        });

        $pdf = Pdf::loadView('exports.stok-bahan-garmen', [
            'data' => $data,
            'title' => 'Laporan Stok Bahan Garmen',
            'tanggal' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('stok_bahan_garmen_' . date('Ymd_His') . '.pdf');
    }
}
