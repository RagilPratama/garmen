<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old enum column and recreate with new roles
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'superadmin',
                'admingudang',
                'adminkantor',
                'adminjomei',
                'adminkamiko'
            ])->default('admingudang')->after('password');
        });

        // Update existing users
        // Map old roles to new roles
        DB::table('users')->where('role', 'admin')->update(['role' => 'superadmin']);
        DB::table('users')->where('role', 'toko_jomei')->update(['role' => 'adminjomei']);
        DB::table('users')->where('role', 'toko_kamiko')->update(['role' => 'adminkamiko']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'toko_jomei', 'toko_kamiko'])->default('admin')->after('password');
        });
    }
};
