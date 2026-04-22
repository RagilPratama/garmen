<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'toko_jomei', 'toko_kamiko'])->default('admin')->after('password');
            $table->foreignId('toko_id')->nullable()->after('role')->constrained('tokos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['toko_id']);
            $table->dropColumn(['role', 'toko_id']);
        });
    }
};
