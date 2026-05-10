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
        Schema::create('barcode_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_code')->unique(); // BRC-xxx
            $table->string('no_surat_jalan')->nullable();
            $table->string('supplier');
            $table->string('kode_bahan');
            $table->string('nama_bahan');
            $table->decimal('quantity', 10, 2); // yard/kg
            $table->string('satuan')->default('yard'); // yard or kg
            $table->decimal('rp_per_yard', 15, 2)->nullable(); // harga bisa diisi nanti
            $table->decimal('total_harga', 15, 2)->nullable();
            $table->date('tanggal');
            $table->boolean('harga_sudah_diisi')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_bahan');
    }
};
