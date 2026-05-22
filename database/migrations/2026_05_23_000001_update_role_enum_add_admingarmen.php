<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum to include 'admingarmen'
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('superadmin','admingarmen','adminkantor','adminjomei','adminkamiko') NOT NULL DEFAULT 'admingarmen'");
    }

    public function down(): void
    {
        // Revert to previous enum that used 'admingudang'
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('superadmin','admingudang','adminkantor','adminjomei','adminkamiko') NOT NULL DEFAULT 'admingudang'");
    }
};
