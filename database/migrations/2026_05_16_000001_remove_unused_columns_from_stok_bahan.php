<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_bahan', function (Blueprint $table) {
            $table->dropColumn(['total_keluar', 'sisa_stok']);
        });

        // Rename total_masuk to quantity agar lebih jelas
        Schema::table('stok_bahan', function (Blueprint $table) {
            $table->renameColumn('total_masuk', 'quantity');
        });
    }

    public function down(): void
    {
        Schema::table('stok_bahan', function (Blueprint $table) {
            $table->renameColumn('quantity', 'total_masuk');
        });

        Schema::table('stok_bahan', function (Blueprint $table) {
            $table->decimal('total_keluar', 12, 2)->default(0)->after('total_masuk');
            $table->decimal('sisa_stok', 12, 2)->default(0)->after('total_keluar');
        });
    }
};
