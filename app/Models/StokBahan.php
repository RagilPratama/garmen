<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBahan extends Model
{
    protected $table = 'stok_bahan';

    protected $fillable = [
        'kode_bahan',
        'total_masuk',
        'total_keluar',
        'sisa_stok',
    ];

    protected $casts = [
        'total_masuk' => 'float',
        'total_keluar' => 'float',
        'sisa_stok'   => 'float',
    ];
}
