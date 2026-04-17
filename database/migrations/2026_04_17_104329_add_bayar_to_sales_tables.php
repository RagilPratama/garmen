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
        Schema::table('proses_jual', function (Blueprint $table) {
            $table->decimal('bayar', 15, 2)->default(0)->after('total_harga');
        });

        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->decimal('bayar', 15, 2)->default(0)->after('total_harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_jual', function (Blueprint $table) {
            $table->dropColumn('bayar');
        });

        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->dropColumn('bayar');
        });
    }
};
