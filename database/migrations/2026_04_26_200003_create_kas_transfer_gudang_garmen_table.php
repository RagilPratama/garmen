<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transfer_gudang_garmen', function (Blueprint $table) {
            $table->id();
            $table->enum('entitas_pengirim', ['gudang', 'garmen']);
            $table->enum('entitas_penerima', ['gudang', 'garmen']);
            $table->date('tanggal');
            $table->enum('metode_bayar', ['cash', 'transfer', 'debit']);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('rekening_id')->nullable()->constrained('rekening')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->index(['tanggal', 'entitas_pengirim', 'entitas_penerima'], 'idx_kas_transfer_gg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transfer_gudang_garmen');
    }
};
