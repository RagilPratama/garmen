<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesFinishing extends Model
{
    protected $table = 'proses_finishing';

    protected $fillable = [
        'po', 'model', 'tanggal_proses', 'pcs', 'tanggal_selesai', 'pcs_barang_jadi',
    ];

    protected $casts = [
        'tanggal_proses' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
