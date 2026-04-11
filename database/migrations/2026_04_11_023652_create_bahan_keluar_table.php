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
        Schema::create('bahan_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_surat_jalan')->nullable();
            $table->string('kode_bahan');
            $table->decimal('yard', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_keluar');
    }
};
