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
        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->decimal('diskon', 5, 2)->default(0)->after('harga_satuan');
        });

        Schema::table('proses_jual', function (Blueprint $table) {
            $table->decimal('diskon', 5, 2)->default(0)->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });

        Schema::table('proses_jual', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });
    }
};
