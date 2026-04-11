<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->string('nama_bahan')->nullable()->after('kode_bahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->dropColumn('nama_bahan');
        });
    }
};
