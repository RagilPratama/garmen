<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->foreignId('master_bahan_id')->nullable()->after('nama_bahan')->constrained('master_bahan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->dropForeign(['master_bahan_id']);
            $table->dropColumn('master_bahan_id');
        });
    }
};
