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
        Schema::create('bahan_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_surat_jalan')->nullable();
            $table->string('no_nota')->nullable();
            $table->string('supplier');
            $table->string('kode_bahan');
            $table->decimal('yard', 10, 2);
            $table->decimal('rp_per_yard', 15, 2);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_masuk');
    }
};
