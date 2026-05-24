<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            // Add satuan column
            $table->string('satuan')->default('yard')->after('yard');

            // Rename yard to quantity
            $table->renameColumn('yard', 'quantity');

            // Rename rp_per_yard to harga_satuan
            $table->renameColumn('rp_per_yard', 'harga_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('bahan_masuk', function (Blueprint $table) {
            $table->renameColumn('quantity', 'yard');
            $table->renameColumn('harga_satuan', 'rp_per_yard');
            $table->dropColumn('satuan');
        });
    }
};
