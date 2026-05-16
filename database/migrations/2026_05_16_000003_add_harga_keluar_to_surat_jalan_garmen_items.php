<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalan_garmen_items', function (Blueprint $table) {
            $table->decimal('harga_keluar', 12, 2)->default(0)->after('quantity');
            $table->decimal('total_harga', 12, 2)->default(0)->after('harga_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalan_garmen_items', function (Blueprint $table) {
            $table->dropColumn(['harga_keluar', 'total_harga']);
        });
    }
};
