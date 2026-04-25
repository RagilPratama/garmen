<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transfer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_pengirim_id')->constrained('tokos')->onDelete('cascade');
            $table->foreignId('toko_penerima_id')->constrained('tokos')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('metode_bayar', ['cash', 'transfer', 'debit']);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['toko_pengirim_id', 'tanggal']);
            $table->index(['toko_penerima_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transfer');
    }
};
