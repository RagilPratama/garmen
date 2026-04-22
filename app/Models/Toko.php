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
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
