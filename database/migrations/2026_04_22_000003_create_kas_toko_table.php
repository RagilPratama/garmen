<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_toko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('tokos')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'keluar']); // masuk = penjualan/setoran, keluar = pengeluaran
            $table->string('kategori'); // penjualan, setoran_awal, pengeluaran_operasional, dll
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->string('referensi')->nullable(); // no_nota jika dari penjualan
            $table->decimal('saldo_sebelum', 15, 2);
            $table->decimal('saldo_sesudah', 15, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['toko_id', 'tanggal']);
            $table->index('referensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_toko');
    }
};
