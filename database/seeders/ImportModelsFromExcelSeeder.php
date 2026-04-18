<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportModelsFromExcelSeeder extends Seeder
{
    /**
     * Import models dari file Excel model.xlsx
     * 
     * Jalankan dengan: php artisan db:seed --class=ImportModelsFromExcelSeeder
     * 
     * File Excel harus memiliki struktur:
     * - Column A: nama_model
     * - Column B: keterangan (opsional)
     */
    public function run(): void
    {
        $filePath = base_path('model.xlsx');

        if (!file_exists($filePath)) {
            echo "❌ File model.xlsx tidak ditemukan di root project!\n";
            return;
        }

        try {
            // Load Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $imported = 0;
            $skipped = 0;

            // Skip header row (row 1)
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header
                
                $namaModel = trim($row[0] ?? '');
                $keterangan = trim($row[1] ?? '');

                // Skip empty rows
                if (empty($namaModel)) {
                    continue;
                }

                // Check if model already exists
                $exists = DB::table('master_models')
                    ->where('nama_model', $namaModel)
                    ->exists();

                if ($exists) {
                    echo "⊘ Model '$namaModel' sudah ada, skip\n";
                    $skipped++;
                    continue;
                }

                // Insert model
                DB::table('master_models')->insert([
                    'nama_model' => $namaModel,
                    'keterangan' => $keterangan ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "✓ Model '$namaModel' berhasil diimport\n";
                $imported++;
            }

            echo "\n✅ Import selesai!\n";
            echo "   Berhasil: $imported\n";
            echo "   Skipped: $skipped\n";

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
