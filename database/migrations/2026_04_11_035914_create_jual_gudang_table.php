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
        Schema::create('jual_gudang', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota')->nullable();
            $table->date('tanggal_nota');
            $table->string('buyer');
            $table->string('po')->nullable();
            $table->string('model');
            $table->integer('pcs');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jual_gudang');
    }
};
