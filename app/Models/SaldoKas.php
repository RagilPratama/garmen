<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoKas extends Model
{
    protected $table = 'saldo_kas';

    protected $fillable = [
        'entitas',
        'saldo_cash',
        'saldo_transfer',
        'saldo_debit',
    ];

    protected $casts = [
        'saldo_cash' => 'decimal:2',
        'saldo_transfer' => 'decimal:2',
        'saldo_debit' => 'decimal:2',
    ];

    public function getTotalSaldoAttribute()
    {
        return $this->saldo_cash + $this->saldo_transfer + $this->saldo_debit;
    }
}
