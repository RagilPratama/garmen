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
        Schema::create('proses_finishing', function (Blueprint $table) {
            $table->id();
            $table->string('po');
            $table->string('model');
            $table->date('tanggal_proses');
            $table->integer('pcs');
            $table->date('tanggal_selesai')->nullable();
            $table->integer('pcs_barang_jadi')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_finishing');
    }
};
