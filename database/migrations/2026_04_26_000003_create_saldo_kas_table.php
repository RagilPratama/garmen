<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_kas', function (Blueprint $table) {
            $table->id();
            $table->enum('entitas', ['gudang', 'garmen']); // gudang = kantor
            $table->decimal('saldo_cash', 15, 2)->default(0);
            $table->decimal('saldo_transfer', 15, 2)->default(0);
            $table->decimal('saldo_debit', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique('entitas');
        });
        
        // Insert default records
        DB::table('saldo_kas')->insert([
            ['entitas' => 'gudang', 'saldo_cash' => 0, 'saldo_transfer' => 0, 'saldo_debit' => 0],
            ['entitas' => 'garmen', 'saldo_cash' => 0, 'saldo_transfer' => 0, 'saldo_debit' => 0],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_kas');
    }
};
