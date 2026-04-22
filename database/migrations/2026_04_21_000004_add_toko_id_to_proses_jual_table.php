<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proses_jual', function (Blueprint $table) {
            // Tambah kolom toko_id untuk identifikasi penjualan dari toko mana
            $table->foreignId('toko_id')->nullable()->after('id')->constrained('tokos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('proses_jual', function (Blueprint $table) {
            $table->dropForeign(['toko_id']);
            $table->dropColumn('toko_id');
        });
    }
};
