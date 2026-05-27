<?php

namespace Database\Seeders;

use App\Models\BahanMasuk;
use App\Models\BarcodeBahan;
use App\Models\MasterBahan;
use App\Models\MasterKepemilikan;
use App\Models\Supplier;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBahanMasukFromExcelSeeder extends Seeder
{
    use GeneratesSuratJalan;
    /**
     * Import bahan masuk dari file Excel DatabaseStock.xlsx
     * 
     * Jalankan dengan: php artisan db:seed --class=ImportBahanMasukFromExcelSeeder
     */
    public function run(): void
    {
        $filePath = base_path('DatabaseStock.xlsx');

        if (!file_exists($filePath)) {
            echo "❌ File DatabaseStock.xlsx tidak ditemukan di root project!\n";
            return;
        }

        try {
            // TRUNCATE tables before import
            echo "Truncating tables...\n";
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            BahanMasuk::truncate();
            DB::table('stok_bahan')->truncate();
            BarcodeBahan::truncate();
            DB::table('tracking_bahan')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            echo "Reading Excel file...\n";
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (empty($rows) || count($rows) < 2) {
                echo "❌ File Excel kosong atau hanya berisi header.\n";
                return;
            }

            $headerInfo = $this->findHeaderRow($rows);
            if (!$headerInfo) {
                echo "❌ Header Excel tidak ditemukan. Pastikan baris header berisi 'Tanggal Bahan Masuk' dan 'Kode Bahan'.\n";
                return;
            }

            $columnMap = $this->mapHeaders($headerInfo['row']);
            if (empty($columnMap)) {
                echo "❌ Header Excel tidak sesuai atau tidak memiliki kolom yang dikenali.\n";
                return;
            }

            $rowKeys = array_keys($rows);
            $headerPos = array_search($headerInfo['index'], $rowKeys, true);
            if ($headerPos === false) {
                echo "❌ Header Excel ditemukan, tapi posisi baris tidak dapat dihitung.\n";
                return;
            }

            $dataStartPos = $headerPos + 1;
            $rows = array_slice($rows, $dataStartPos);
            $dataRowKeys = array_slice($rowKeys, $dataStartPos);

            // Pre-load Master Data
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
                $masterKepemilikanMap[$this->normalizeString($masterKepemilikan->nama_kepemilikan)] = [
                    'id' => $masterKepemilikan->id,
                    'nama_kepemilikan' => $masterKepemilikan->nama_kepemilikan,
                ];
            }

            $supplierRecords = Supplier::orderBy('nama')->get(['id', 'nama']);
            $supplierMap = [];
            foreach ($supplierRecords as $supplier) {
                $supplierMap[$this->normalizeString($supplier->nama)] = [
                    'id' => $supplier->id,
                    'nama' => $supplier->nama,
                ];
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $stokDeltas = [];

            DB::transaction(function () use ($rows, $dataRowKeys, $columnMap, $masterBahanMap, $masterKepemilikanMap, $supplierMap, &$imported, &$skipped, &$errors, &$stokDeltas) {
                foreach ($rows as $index => $row) {
                    $rowNumber = $dataRowKeys[$index] ?? ($index + 1);

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
                        
                        // 1. Resolve Master Bahan (Jenis Bahan)
                        $rawJenisBahan = trim((string) ($data['jenis_bahan'] ?? $data['nama_bahan'] ?? ''));
                        $resolvedMaster = $this->resolveMasterData($rawJenisBahan, $masterBahanMap);
                        $masterBahanId = $resolvedMaster['id'] ?? null;
                        $namaBahan = $resolvedMaster['nama_bahan'] ?? ($rawJenisBahan !== '' ? $rawJenisBahan : null);
                        
                        // 2. Resolve Master Kepemilikan (Pemilik)
                        $rawPemilik = trim((string) ($data['pemilik'] ?? ''));
                        $resolvedKepemilikan = $this->resolveMasterData($rawPemilik, $masterKepemilikanMap, 'nama_kepemilikan');
                        $masterKepemilikanId = $resolvedKepemilikan['id'] ?? null;
                        
                        // 3. Resolve Supplier (Penyedia)
                        $rawSupplier = trim((string) ($data['supplier'] ?? ''));
                        $resolvedSupplier = $this->resolveMasterData($rawSupplier, $supplierMap, 'nama');
                        
                        // Final Supplier Name Logic:
                        // Jika ada di Master Supplier, gunakan itu. 
                        // Jika tidak, jika ada di Master Kepemilikan (sebagai pemilik), gunakan itu.
                        // Jika tidak ada keduanya, gunakan raw string dari kolom supplier/pemilik.
                        $supplierName = $resolvedSupplier['nama'] 
                                        ?? $resolvedKepemilikan['nama_kepemilikan'] 
                                        ?? ($rawSupplier !== '' ? $rawSupplier : ($rawPemilik !== '' ? $rawPemilik : 'Gudang'));
                        
                        $quantity = $this->resolveQuantity($data);
                        $satuan = $this->resolveSatuan($data) ?: 'yard';
                        $hargaSatuan = (float) ($data['harga_satuan'] ?? 0);
                        $total = isset($data['total']) ? (float) $data['total'] : ($quantity * $hargaSatuan);
                        
                        $noNota = trim((string) ($data['no_nota'] ?? '')) ?: $this->nextCode(BahanMasuk::class, 'no_nota', 'NT-');
                        $noSuratJalan = trim((string) ($data['no_surat_jalan'] ?? null)) ?: null;

                        if ($quantity <= 0) {
                            $errors[] = "Baris {$rowNumber}: quantity tidak valid ($quantity).";
                            continue;
                        }

                        if (!$tanggal) {
                            $tanggal = now()->format('Y-m-d');
                        }

                        BahanMasuk::create([
                            'tanggal' => $tanggal,
                            'no_surat_jalan' => $noSuratJalan,
                            'no_nota' => $noNota,
                            'supplier' => $supplierName,
                            'kode_bahan' => $kodeBahan,
                            'nama_bahan' => $namaBahan,
                            'master_bahan_id' => $masterBahanId,
                            'master_kepemilikan_id' => $masterKepemilikanId,
                            'quantity' => $quantity,
                            'satuan' => $satuan,
                            'harga_satuan' => $hargaSatuan,
                            'total' => $total,
                        ]);

                        // 4. Create BarcodeBahan (agar bisa dipindah ke garmen/scan-ready)
                        BarcodeBahan::create([
                            'barcode_code' => $kodeBahan,
                            'no_surat_jalan' => $noSuratJalan,
                            'supplier' => $supplierName,
                            'kode_bahan' => $kodeBahan,
                            'nama_bahan' => $namaBahan,
                            'quantity' => $quantity,
                            'satuan' => $satuan,
                            'rp_per_yard' => $hargaSatuan,
                            'harga_keluar' => 0, // Default 0, bisa diupdate nanti
                            'total_harga' => $total,
                            'tanggal' => $tanggal,
                            'harga_sudah_diisi' => $hargaSatuan > 0,
                            'kepemilikan_id' => $masterKepemilikanId,
                        ]);

                        // 5. Create initial tracking (lokasi: gudang)
                        DB::table('tracking_bahan')->insert([
                            'kode_bahan' => $kodeBahan,
                            'lokasi' => 'gudang',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $stokDeltas[$kodeBahan] = ($stokDeltas[$kodeBahan] ?? 0) + $quantity;
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                    }
                }

                $this->bulkAddStok($stokDeltas);
            });

            echo "\n✅ Import selesai!\n";
            echo "   Berhasil: $imported\n";
            echo "   Skipped: $skipped (Kode bahan kosong)\n";
            if (!empty($errors)) {
                echo "   Gagal: " . count($errors) . "\n";
                foreach (array_slice($errors, 0, 5) as $error) {
                    echo "     - $error\n";
                }
            }

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
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

    private function mapHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $value) {
            $normalized = $this->normalizeString((string) $value);
            switch ($normalized) {
                case 'tanggal bahan masuk':
                case 'tanggal bahan':
                case 'tgl':
                case 'tanggal':
                    $map[$col] = 'tanggal';
                    break;
                case 'kode bahan':
                case 'kode':
                    $map[$col] = 'kode_bahan';
                    break;
                case 'nama bahan':
                case 'jenis bahan':
                case 'jenis_bahan':
                    $map[$col] = 'jenis_bahan';
                    break;
                case 'supplier':
                case 'pengirim':
                case 'supplier/pengirim':
                    $map[$col] = 'supplier';
                    break;
                case 'no nota':
                case 'no_nota':
                case 'nota':
                    $map[$col] = 'no_nota';
                    break;
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
                    $map[$col] = 'quantity';
                    break;
                case 'satuan':
                    $map[$col] = 'satuan';
                    break;
                case 'harga satuan':
                case 'harga_satuan':
                case 'harga':
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
                    $map[$col] = 'no_surat_jalan';
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

        if ($yard > 0) return $yard;
        if ($meter > 0) return $meter;
        if ($kg > 0) return $kg;

        return $quantity;
    }

    private function resolveSatuan(array $data): ?string
    {
        if (isset($data['yard']) && trim((string) $data['yard']) !== '') return 'yard';
        if (isset($data['meter']) && trim((string) $data['meter']) !== '') return 'meter';
        if (isset($data['kg']) && trim((string) $data['kg']) !== '') return 'kg';
        return $data['satuan'] ?? null;
    }

    private function normalizeString(?string $value): string
    {
        if ($value === null) return '';
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim($normalized);
    }

    private function resolveMasterData(?string $rawValue, array $masterMap, string $nameKey = 'nama_bahan'): array
    {
        $raw = $this->normalizeString($rawValue);
        if ($raw === '') return ['id' => null, $nameKey => null];

        // Apply corrections first for specific typs
        $corrected = $this->applyKnownNameCorrections($raw);

        // Exact match in map
        if (isset($masterMap[$corrected])) {
            return $masterMap[$corrected];
        }

        return ['id' => null, $nameKey => null];
    }

    private function applyKnownNameCorrections(string $value): string
    {
        $corrections = [
            'deniim' => 'denim',
            'twil' => 'twill',
            'codoray' => 'corduray',
            'coduray' => 'corduray',
            'cordurai' => 'corduray',
            'motig orange' => 'motif orange',
            'sikabu tex codoray' => 'sikabu tex corduray',
        ];
        return $corrections[$value] ?? $value;
    }

    private function bulkAddStok(array $deltas): void
    {
        if (empty($deltas)) return;

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
