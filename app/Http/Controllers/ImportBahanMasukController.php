<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\MasterBahan;
use App\Models\MasterKepemilikan;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBahanMasukController extends Controller
{
    use GeneratesSuratJalan;

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (empty($rows) || count($rows) < 2) {
                return back()->with('message', 'File Excel kosong atau hanya berisi header.');
            }

            $headerInfo = $this->findHeaderRow($rows);
            if (!$headerInfo) {
                return back()->with('message', 'Header Excel tidak ditemukan. Pastikan baris header berisi "Tanggal Bahan Masuk" dan "Kode Bahan".');
            }

            $columnMap = $this->mapHeaders($headerInfo['row']);
            if (empty($columnMap)) {
                return back()->with('message', 'Header Excel tidak sesuai atau tidak memiliki kolom yang dikenali.');
            }

            $rows = array_slice($rows, $headerInfo['index'] + 1);

            $masterBahanRecords = MasterBahan::orderBy('nama_bahan')->get(['id', 'nama_bahan']);
            $masterBahanMap = [];
            foreach ($masterBahanRecords as $masterBahan) {
                $masterBahanMap[$this->normalizeString($masterBahan->nama_bahan)] = [
                    'id' => $masterBahan->id,
                    'nama_bahan' => $masterBahan->nama_bahan,
                ];
            }

            $masterKepemilikanRecords = MasterKepemilikan::orderBy('nama_kepemilikan')->get(['id', 'nama_kepemilikan']);
            $masterKepemilikanMap = [];
            foreach ($masterKepemilikanRecords as $masterKepemilikan) {
                $masterKepemilikanMap[$this->normalizeString($masterKepemilikan->nama_kepemilikan)] = $masterKepemilikan->id;
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $stokDeltas = [];

            DB::transaction(function () use ($rows, $columnMap, $masterBahanMap, &$imported, &$skipped, &$errors, &$stokDeltas) {
                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 2;

                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    $data = $this->mapRow($row, $columnMap);

                    if (empty($data['kode_bahan'])) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $tanggal = $this->parseDate($data['tanggal'] ?? null);
                        $kodeBahan = trim((string) ($data['kode_bahan'] ?? ''));
                        $rawJenisBahan = trim((string) ($data['jenis_bahan'] ?? $data['nama_bahan'] ?? ''));
                        $resolvedMaster = $this->resolveJenisBahan($rawJenisBahan, $masterBahanMap);
                        $masterBahanId = $resolvedMaster['id'] ?? null;
                        $namaBahan = $resolvedMaster['nama_bahan'] ?? ($rawJenisBahan !== '' ? $rawJenisBahan : null);
                        $rawSupplier = trim((string) ($data['supplier'] ?? ''));
                        $rawPemilik = trim((string) ($data['pemilik'] ?? ''));
                        $supplier = $rawSupplier !== '' ? $rawSupplier : ($rawPemilik !== '' ? $rawPemilik : 'Gudang');
                        $masterKepemilikanId = $this->resolveKepemilikan($rawPemilik, $masterKepemilikanMap);
                        $quantity = $this->resolveQuantity($data);
                        $satuan = $this->resolveSatuan($data) ?: 'yard';
                        $hargaSatuan = (float) ($data['harga_satuan'] ?? 0);
                        $total = isset($data['total']) ? (float) $data['total'] : ($quantity * $hargaSatuan);
                        $noNota = trim((string) ($data['no_nota'] ?? '')) ?: $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');
                        $noSuratJalan = trim((string) ($data['no_surat_jalan'] ?? null)) ?: null;

                        if ($quantity <= 0) {
                            $errors[] = "Baris {$rowNumber}: quantity tidak valid.";
                            continue;
                        }

                        if (!$tanggal) {
                            $tanggal = now()->format('Y-m-d');
                        }

                        BahanMasuk::create([
                            'tanggal' => $tanggal ?: now()->format('Y-m-d'),
                            'no_surat_jalan' => $noSuratJalan,
                            'no_nota' => $noNota,
                            'supplier' => $supplier,
                            'kode_bahan' => $kodeBahan,
                            'nama_bahan' => $namaBahan,
                            'master_bahan_id' => $masterBahanId,
                            'master_kepemilikan_id' => $masterKepemilikanId,
                            'quantity' => $quantity,
                            'satuan' => $satuan,
                            'harga_satuan' => $hargaSatuan,
                            'total' => $total,
                        ]);

                        $stokDeltas[$kodeBahan] = ($stokDeltas[$kodeBahan] ?? 0) + $quantity;
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                    }
                }

                $this->bulkAddStok($stokDeltas);
            });

            $message = "Import selesai. {$imported} baris disimpan.";
            if ($skipped > 0) {
                $message .= " {$skipped} baris dilewati karena kode bahan kosong.";
            }
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' baris gagal diproses.';
            }

            return back()->with('message', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error import Excel: ' . $e->getMessage()]);
        }
    }

    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $col => $value) {
            $key = trim(strtolower((string) $value));

            if ($key === '') {
                continue;
            }

            switch ($key) {
                case 'no nota':
                case 'no_nota':
                case 'nota':
                case 'nomor nota':
                case 'no nota supplier':
                    $map[$col] = 'no_nota';
                    break;
                case 'tanggal':
                case 'tanggal_nota':
                case 'tanggal nota':
                case 'tgl':
                case 'tgl nota':
                    $map[$col] = 'tanggal';
                    break;
                case 'supplier':
                    $map[$col] = 'supplier';
                    break;
                case 'kode bahan':
                case 'kode_bahan':
                case 'kode':
                    $map[$col] = 'kode_bahan';
                    break;
                case 'nama bahan':
                case 'nama_bahan':
                case 'nama':
                    $map[$col] = 'nama_bahan';
                    break;
                case 'jenis bahan':
                case 'jenis_bahan':
                case 'jenisbahan':
                    $map[$col] = 'jenis_bahan';
                    break;
                case 'tanggal bahan masuk':
                case 'tanggal_bahan_masuk':
                case 'tanggal bahan':
                    $map[$col] = 'tanggal';
                    break;
                case 'no. surat jalan supplier':
                case 'no surat jalan supplier':
                case 'no_surat_jalan_supplier':
                    $map[$col] = 'no_surat_jalan';
                    break;
                case 'pemilik':
                    $map[$col] = 'pemilik';
                    break;
                case 'meter':
                    $map[$col] = 'meter';
                    break;
                case 'yard':
                    $map[$col] = 'yard';
                    break;
                case 'kg':
                    $map[$col] = 'kg';
                    break;
                case 'quantity':
                case 'qty':
                case 'jumlah':
                case 'jumlah bahan':
                    $map[$col] = 'quantity';
                    break;
                case 'satuan':
                    $map[$col] = 'satuan';
                    break;
                case 'harga satuan':
                case 'harga_satuan':
                case 'harga':
                case 'harga per yard':
                case 'harga / yard':
                case 'rp_per_yard':
                    $map[$col] = 'harga_satuan';
                    break;
                case 'total':
                case 'total harga':
                case 'total_harga':
                    $map[$col] = 'total';
                    break;
                case 'no surat jalan':
                case 'no_surat_jalan':
                case 'surat jalan':
                case 'no sj':
                    $map[$col] = 'no_surat_jalan';
                    break;
                default:
                    // Ignored columns: packing list, saldo, suratjalan, and any other unknown headers.
                    break;
            }
        }

        return $map;
    }

    private function mapRow(array $row, array $columnMap): array
    {
        $result = [];

        foreach ($columnMap as $col => $field) {
            $result[$field] = trim((string) ($row[$col] ?? ''));
        }

        return $result;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            $unix = ($value - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function resolveQuantity(array $data): float
    {
        $yard = isset($data['yard']) ? (float) $data['yard'] : 0;
        $meter = isset($data['meter']) ? (float) $data['meter'] : 0;
        $kg = isset($data['kg']) ? (float) $data['kg'] : 0;
        $quantity = isset($data['quantity']) ? (float) $data['quantity'] : 0;

        if ($yard > 0) {
            return $yard;
        }

        if ($meter > 0) {
            return $meter;
        }

        if ($kg > 0) {
            return $kg;
        }

        return $quantity;
    }

    private function resolveSatuan(array $data): ?string
    {
        if (isset($data['yard']) && trim((string) $data['yard']) !== '') {
            return 'yard';
        }

        if (isset($data['meter']) && trim((string) $data['meter']) !== '') {
            return 'meter';
        }

        if (isset($data['kg']) && trim((string) $data['kg']) !== '') {
            return 'kg';
        }

        return $data['satuan'] ?? null;
    }

    private function resolveJenisBahan(?string $rawValue, array $masterBahanMap): ?array
    {
        $raw = $this->normalizeString($rawValue);
        if ($raw === '') {
            return null;
        }

        $raw = $this->applyKnownNameCorrections($raw);

        if (isset($masterBahanMap[$raw])) {
            return $masterBahanMap[$raw];
        }

        foreach ($masterBahanMap as $normalized => $masterBahan) {
            if (str_contains($normalized, $raw) || str_contains($raw, $normalized)) {
                return $masterBahan;
            }
        }

        $best = null;
        $bestScore = 0;
        foreach ($masterBahanMap as $normalized => $masterBahan) {
            similar_text($raw, $normalized, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $masterBahan;
            }
        }

        return $bestScore >= 65 ? $best : null;
    }

    private function resolveKepemilikan(?string $rawValue, array $masterMap): ?int
    {
        if (!$rawValue) {
            return null;
        }

        $raw = $this->normalizeString($rawValue);
        if ($raw === '') {
            return null;
        }

        if (isset($masterMap[$raw])) {
            return $masterMap[$raw];
        }

        foreach ($masterMap as $normalized => $id) {
            if (str_contains($normalized, $raw) || str_contains($raw, $normalized)) {
                return $id;
            }
        }

        $bestId = null;
        $bestScore = 0;
        foreach ($masterMap as $normalized => $id) {
            similar_text($raw, $normalized, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestId = $id;
            }
        }

        return $bestScore >= 65 ? $bestId : null;
    }

    private function applyKnownNameCorrections(string $value): string
    {
        $corrections = [
            'deniim' => 'denim',
            'twil' => 'twill',
            'twill ht 180' => 'twill ht 180',
            'codoray' => 'corduray',
            'coduray' => 'corduray',
            'cordurai' => 'corduray',
            'motig orange' => 'motif orange',
            'sikabu tex codoray' => 'sikabu tex corduray',
        ];

        return $corrections[$value] ?? $value;
    }

    private function findHeaderRow(array $rows): ?array
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn($value) => $this->normalizeString((string) $value), $row);
            $hasTanggal = in_array('tanggal bahan masuk', $normalized, true)
                || in_array('tanggal bahan', $normalized, true)
                || in_array('tgl', $normalized, true);
            $hasKode = in_array('kode bahan', $normalized, true)
                || in_array('kode', $normalized, true);

            if ($hasTanggal && $hasKode) {
                return ['row' => $row, 'index' => $index];
            }
        }

        return null;
    }

    private function normalizeString(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
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
