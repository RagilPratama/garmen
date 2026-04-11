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
        Schema::create('barang_kirim_toko', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat_jalan')->nullable();
            $table->date('tanggal_kirim');
            $table->string('po');
            $table->string('model');
            $table->integer('pcs_barang_jadi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_kirim_toko');
    }
};
