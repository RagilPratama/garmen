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
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->foreignId('master_kepemilikan_id')->nullable()->after('master_bahan_id')->constrained('master_kepemilikans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->dropForeign(['master_kepemilikan_id']);
            $table->dropColumn('master_kepemilikan_id');
        });
    }
};
