<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bahan')->unique();
            $table->enum('lokasi', ['gudang', 'garmen'])->default('gudang');
            $table->timestamps();
        });

        DB::statement(
            "
            INSERT INTO tracking_bahan (kode_bahan, lokasi, created_at, updated_at)
            SELECT
                bb.kode_bahan,
                CASE WHEN sgi.id IS NULL THEN 'gudang' ELSE 'garmen' END AS lokasi,
                NOW(),
                NOW()
            FROM barcode_bahan bb
            LEFT JOIN surat_jalan_garmen_items sgi ON sgi.barcode_bahan_id = bb.id
            WHERE bb.kode_bahan IS NOT NULL
            ON DUPLICATE KEY UPDATE
                lokasi = VALUES(lokasi),
                updated_at = NOW()
            ",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_bahan');
    }
};
