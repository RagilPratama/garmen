<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_keluar', function (Blueprint $table) {
            $table->decimal('rp_per_yard', 15, 2)->default(0)->after('yard');
            $table->decimal('total', 15, 2)->default(0)->after('rp_per_yard');
        });
    }

    public function down(): void
    {
        Schema::table('bahan_keluar', function (Blueprint $table) {
            $table->dropColumn(['rp_per_yard', 'total']);
        });
    }
};
