<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota');
            $table->string('channel')->default('gudang'); // gudang | toko
            $table->date('tanggal_bayar');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode')->default('cash'); // cash | transfer | debit
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['no_nota', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_pembayaran');
    }
};
