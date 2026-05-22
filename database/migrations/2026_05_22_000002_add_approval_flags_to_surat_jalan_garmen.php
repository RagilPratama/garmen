<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalan_garmen', function (Blueprint $table) {
            $table->boolean('marker_approved')->default(false)->after('keterangan');
            $table->boolean('pola_approved')->default(false)->after('marker_approved');
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalan_garmen', function (Blueprint $table) {
            $table->dropColumn(['marker_approved', 'pola_approved']);
        });
    }
};
