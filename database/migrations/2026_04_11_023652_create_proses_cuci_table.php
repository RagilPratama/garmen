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
        Schema::create('proses_cuci', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_kirim_cuci');
            $table->string('no_surat_jalan')->nullable();
            $table->string('po');
            $table->string('model');
            $table->integer('pcs_kirim');
            $table->date('tanggal_kembali_dari_cuci')->nullable();
            $table->integer('pcs_kembali')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_cuci');
    }
};
