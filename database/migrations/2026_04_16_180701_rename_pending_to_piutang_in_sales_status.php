<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update data lama: ubah 'pending' → 'piutang' di kedua tabel
        DB::table('jual_gudang')->where('status', 'pending')->update(['status' => 'piutang']);
        DB::table('proses_jual')->where('status', 'pending')->update(['status' => 'piutang']);

        // Ubah default value kolom status
        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->string('status')->default('piutang')->change();
        });

        Schema::table('proses_jual', function (Blueprint $table) {
            $table->string('status')->default('piutang')->change();
        });
    }

    public function down(): void
    {
        DB::table('jual_gudang')->where('status', 'piutang')->update(['status' => 'pending']);
        DB::table('proses_jual')->where('status', 'piutang')->update(['status' => 'pending']);

        Schema::table('jual_gudang', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        Schema::table('proses_jual', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
