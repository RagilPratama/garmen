<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_toko', function (Blueprint $table) {
            $table->enum('metode_bayar', ['cash', 'transfer', 'debit'])->default('cash')->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('kas_toko', function (Blueprint $table) {
            $table->dropColumn('metode_bayar');
        });
    }
};
