<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_models', function (Blueprint $table) {
            // Add index untuk kolom nama_model yang sering di-search
            $table->index('nama_model', 'idx_master_models_nama');
        });
    }

    public function down(): void
    {
        Schema::table('master_models', function (Blueprint $table) {
            $table->dropIndex('idx_master_models_nama');
        });
    }
};
