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
        Schema::table('proses_finishing', function (Blueprint $table) {
            $table->bigInteger('harga')->nullable()->after('pcs_barang_jadi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_finishing', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }
};
