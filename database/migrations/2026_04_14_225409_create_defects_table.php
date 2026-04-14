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
        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->string('sumber'); // 'jahit' | 'cuci' | 'finishing'
            $table->string('po');
            $table->string('model');
            $table->integer('pcs_defect');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('referensi_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defects');
    }
};
