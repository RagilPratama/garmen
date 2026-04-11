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
        Schema::create('proses_jahit', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_jahit');
            $table->string('po');
            $table->string('model');
            $table->integer('pcs_potongan');
            $table->date('tanggal_selesai_jahit')->nullable();
            $table->integer('pcs_hasil_jahit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_jahit');
    }
};
