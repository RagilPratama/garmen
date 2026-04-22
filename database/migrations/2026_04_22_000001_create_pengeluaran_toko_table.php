<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran_toko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('tokos')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('kategori'); // plastik, upah_tukang, listrik, air, sewa, lainnya
            $table->string('keterangan');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode_bayar')->default('cash'); // cash, transfer, debit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_toko');
    }
};
