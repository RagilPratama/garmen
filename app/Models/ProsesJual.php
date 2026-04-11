<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesJual extends Model
{
    protected $table = 'proses_jual';

    protected $fillable = [
        'no_nota', 'tanggal_nota', 'buyer', 'model',
        'pcs', 'harga_satuan', 'total_harga', 'status',
    ];

    protected $casts = [
        'tanggal_nota' => 'date',
    ];
}
