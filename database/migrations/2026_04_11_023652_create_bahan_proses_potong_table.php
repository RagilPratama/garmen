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
        Schema::create('bahan_proses_potong', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_potong');
            $table->decimal('yard', 10, 2);
            $table->string('po');
            $table->string('model');
            $table->string('kode_bahan');
            $table->integer('hasil_potongan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_proses_potong');
    }
};
