<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_gudang', function (Blueprint $table) {
            $table->foreignId('rekening_id')->nullable()->after('metode_bayar')->constrained('rekening')->nullOnDelete();
        });

        Schema::table('kas_garmen', function (Blueprint $table) {
            $table->foreignId('rekening_id')->nullable()->after('metode_bayar')->constrained('rekening')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kas_gudang', function (Blueprint $table) {
            $table->dropForeign(['rekening_id']);
            $table->dropColumn('rekening_id');
        });

        Schema::table('kas_garmen', function (Blueprint $table) {
            $table->dropForeign(['rekening_id']);
            $table->dropColumn('rekening_id');
        });
    }
};
