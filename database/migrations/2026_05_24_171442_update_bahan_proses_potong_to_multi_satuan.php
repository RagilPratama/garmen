<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bahan_proses_potong', function (Blueprint $table) {
            // Rename yard to quantity
            $table->renameColumn('yard', 'quantity');

            // Add satuan column with default 'yard'
            $table
                ->enum('satuan', ['yard', 'meter', 'kg'])
                ->default('yard')
                ->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('bahan_proses_potong', function (Blueprint $table) {
            $table->dropColumn('satuan');
            $table->renameColumn('quantity', 'yard');
        });
    }
};
