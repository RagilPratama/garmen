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
        Schema::table('master_bahan', function (Blueprint $table) {
            $table->string('nama_bahan')->unique()->after('id');
            $table->string('keterangan')->nullable()->after('nama_bahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_bahan', function (Blueprint $table) {
            $table->dropColumn(['nama_bahan', 'keterangan']);
        });
    }
};
