<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_jalan_garmen', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat_jalan')->unique();
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('surat_jalan_garmen_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_jalan_garmen_id')->constrained('surat_jalan_garmen')->cascadeOnDelete();
            $table->foreignId('barcode_bahan_id')->constrained('barcode_bahan')->cascadeOnDelete();
            $table->string('kode_bahan');
            $table->string('nama_bahan')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('satuan')->default('yard');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_jalan_garmen_items');
        Schema::dropIfExists('surat_jalan_garmen');
    }
};
