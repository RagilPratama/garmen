<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->decimal('saldo_cash', 15, 2)->default(0)->after('saldo_kas');
            $table->decimal('saldo_transfer', 15, 2)->default(0)->after('saldo_cash');
            $table->decimal('saldo_debit', 15, 2)->default(0)->after('saldo_transfer');
        });
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['saldo_cash', 'saldo_transfer', 'saldo_debit']);
        });
    }
};
