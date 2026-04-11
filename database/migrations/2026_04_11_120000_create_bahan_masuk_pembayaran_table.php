<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_masuk_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode')->default('cash'); // cash | transfer
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index('no_nota');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_masuk_pembayaran');
    }
};
