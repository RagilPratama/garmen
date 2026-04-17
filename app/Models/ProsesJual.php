<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesJual extends Model
{
    protected $table = 'proses_jual';

    protected $fillable = [
        'no_nota', 'tanggal_nota', 'buyer', 'model',
        'pcs', 'harga_satuan', 'diskon', 'total_harga', 'status', 'bayar',
    ];

    protected $casts = [
        'tanggal_nota' => 'date',
    ];
}
