<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalan_garmen', function (Blueprint $table) {
            $table->boolean('superadmin_allow_print')->default(false)->after('pola_approved');
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalan_garmen', function (Blueprint $table) {
            $table->dropColumn('superadmin_allow_print');
        });
    }
};
