<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JualGudang extends Model
{
    protected $table = 'jual_gudang';

    protected $fillable = [
        'no_nota', 'tanggal_nota', 'buyer', 'po', 'model',
        'pcs', 'harga_satuan', 'total_harga', 'status',
    ];

    protected $casts = [
        'tanggal_nota' => 'date',
    ];
}
