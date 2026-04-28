<?php

namespace App\Http\Controllers;

use App\Models\ProsesJual;
use App\Models\PenjualanPembayaran;
use App\Models\KasToko;
use App\Models\Toko;
use App\Models\MasterModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProsesJualController extends Controller
{
    public function showForm()
    {
        return inertia('Import/ProsesJual', [
            'tokos' => Toko::orderBy('nama_toko')->get(['id', 'nama_toko']),
            'models' => MasterModel::orderBy('nama_model')->pluck('nama_model'),
        ]);
    }
    
    public function exportModels()
    {
        $models = MasterModel::orderBy('nama_model')->get(['nama_model', 'keterangan']);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->fromArray(['nama_model', 'keterangan'], null, 'A1');
        
        $row = 2;
        foreach ($models as $model) {
            $sheet->fromArray([$model->nama_model, $model->keterangan], null, "A{$row}");
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'master_models_' . date('Ymd_His') . '.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);
        
        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'toko_id' => 'required|exists:tokos,id',
        ]);

        $file = $request->file('file');
        $tokoId = $request->toko_id;

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header
            array_shift($rows);

            $imported = 0;
            $errors = [];

            DB::transaction(function () use ($rows, $tokoId, &$imported, &$errors) {
                foreach ($rows as $index => $row) {
                    $rowNum = $index + 2;
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) continue;

                    try {
                        // Parse row data
                        $noNota = $row[0] ?? null;
                        $tanggalNota = $this->parseDate($row[1] ?? null);
                        $buyer = $row[2] ?? null;
                        $model = $row[3] ?? null;
                        $pcs = (int) ($row[4] ?? 0);
                        $hargaSatuan = (float) ($row[5] ?? 0);
                        $diskon = (float) ($row[6] ?? 0);
                        $totalHarga = (float) ($row[7] ?? 0);
                        $status = $row[8] ?? 'piutang';
                        
                        // Pembayaran (optional)
                        $tanggalBayar = $this->parseDate($row[9] ?? null);
                        $jumlahBayar = (float) ($row[10] ?? 0);
                        $metodeBayar = $row[11] ?? 'cash';
                        $keteranganBayar = $row[12] ?? null;

                        // Validasi
                        if (!$noNota || !$tanggalNota || !$buyer || !$model || $pcs <= 0 || $hargaSatuan <= 0) {
                            $errors[] = "Baris {$rowNum}: Data tidak lengkap";
                            continue;
                        }

                        // Insert proses_jual
                        $prosesJual = ProsesJual::create([
                            'toko_id' => $tokoId,
                            'no_nota' => $noNota,
                            'tanggal_nota' => $tanggalNota,
                            'buyer' => $buyer,
                            'model' => $model,
                            'pcs' => $pcs,
                            'harga_satuan' => $hargaSatuan,
                            'diskon' => $diskon,
                            'total_harga' => $totalHarga > 0 ? $totalHarga : ($pcs * $hargaSatuan * (1 - $diskon / 100)),
                            'status' => $status,
                            'bayar' => $jumlahBayar,
                        ]);

                        // Insert pembayaran jika ada
                        if ($jumlahBayar > 0 && $tanggalBayar) {
                            PenjualanPembayaran::create([
                                'no_nota' => $noNota,
                                'channel' => 'toko',
                                'tanggal_bayar' => $tanggalBayar,
                                'jumlah' => $jumlahBayar,
                                'metode' => $metodeBayar,
                                'keterangan' => $keteranganBayar ?? 'Import data',
                            ]);

                            // Update kas toko - LOCK per transaksi
                            $toko = Toko::lockForUpdate()->findOrFail($tokoId);
                            $saldoField = 'saldo_' . $metodeBayar;
                            $saldoSebelum = $toko->$saldoField;
                            $saldoSesudah = $saldoSebelum + $jumlahBayar;

                            KasToko::create([
                                'toko_id' => $tokoId,
                                'tanggal' => $tanggalBayar,
                                'jenis' => 'masuk',
                                'metode_bayar' => $metodeBayar,
                                'kategori' => 'Penjualan',
                                'jumlah' => $jumlahBayar,
                                'keterangan' => "Import: {$noNota} - {$buyer}",
                                'referensi' => $noNota,
                                'saldo_sebelum' => $saldoSebelum,
                                'saldo_sesudah' => $saldoSesudah,
                                'user_id' => auth()->id(),
                            ]);

                            $toko->$saldoField = $saldoSesudah;
                            $toko->saldo_kas = $toko->saldo_cash + $toko->saldo_transfer + $toko->saldo_debit;
                            $toko->save();
                        }

                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$rowNum}: " . $e->getMessage();
                    }
                }
            });

            $message = "{$imported} data berhasil diimport.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " error: " . implode(', ', array_slice($errors, 0, 5));
            }

            return redirect()->route('proses-jual.index')->with('message', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function parseDate($value)
    {
        if (!$value) return null;
        
        // Excel date number
        if (is_numeric($value)) {
            $unix = ($value - 25569) * 86400;
            return date('Y-m-d', $unix);
        }
        
        // String date
        return date('Y-m-d', strtotime($value));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'no_nota',
            'tanggal_nota',
            'buyer',
            'model',
            'pcs',
            'harga_satuan',
            'diskon',
            'total_harga',
            'status',
            'tanggal_bayar',
            'jumlah_bayar',
            'metode_bayar',
            'keterangan_bayar',
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Example data
        $sheet->fromArray([
            'NT-TOKO-001',
            '2026-04-01',
            'Toko ABC',
            'Model A',
            10,
            50000,
            0,
            500000,
            'lunas',
            '2026-04-01',
            500000,
            'cash',
            'Pembayaran lunas',
        ], null, 'A2');

        $sheet->fromArray([
            'NT-TOKO-002',
            '2026-04-02',
            'Toko XYZ',
            'Model B',
            5,
            100000,
            10,
            450000,
            'piutang',
            '2026-04-02',
            200000,
            'transfer',
            'DP 200rb',
        ], null, 'A3');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'template_import_proses_jual.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }
}
