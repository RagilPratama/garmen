<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    protected $fillable = [
        'nama_toko',
        'kode_toko',
        'alamat',
        'telepon',
        'is_active',
        'saldo_kas',
        'saldo_cash',
        'saldo_transfer',
        'saldo_debit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'saldo_kas' => 'decimal:2',
        'saldo_cash' => 'decimal:2',
        'saldo_transfer' => 'decimal:2',
        'saldo_debit' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function barangKirimToko(): HasMany
    {
        return $this->hasMany(BarangKirimToko::class);
    }

    public function prosesJual(): HasMany
    {
        return $this->hasMany(ProsesJual::class);
    }

    public function kasToko(): HasMany
    {
        return $this->hasMany(KasToko::class);
    }
}
