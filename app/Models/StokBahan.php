<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBahan extends Model
{
    protected $table = 'stok_bahan';

    protected $fillable = [
        'kode_bahan',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];
}
