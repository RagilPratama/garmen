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

            $dataToInsert = [];
            $imported = 0;
            $skipped = 0;
            $existingModels = DB::table('master_models')->pluck('nama_model')->map(fn($name) => strtolower($name))->toArray();
            $existingModelsMap = array_flip($existingModels);

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header
                
                $namaModel = trim($row[0] ?? '');
                $keterangan = trim($row[1] ?? '');

                // Skip empty rows
                if (empty($namaModel)) {
                    continue;
                }

                // Check if model already exists using the map (case-insensitive)
                $nameKey = strtolower($namaModel);
                if (isset($existingModelsMap[$nameKey])) {
                    $skipped++;
                    continue;
                }

                // Add to bulk insert array
                $dataToInsert[] = [
                    'nama_model' => $namaModel,
                    'keterangan' => $keterangan ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Track as imported
                $imported++;
                
                // Add to map to prevent duplicates within the Excel file itself
                $existingModelsMap[$nameKey] = true;
            }

            // Perform bulk insert in chunks of 500
            if (!empty($dataToInsert)) {
                $chunks = array_chunk($dataToInsert, 500);
                foreach ($chunks as $chunk) {
                    DB::table('master_models')->insertOrIgnore($chunk);
                }
            }

            echo "\n✅ Import selesai!\n";
            echo "   Berhasil: $imported\n";
            echo "   Skipped: $skipped (Sudah ada di database)\n";

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
