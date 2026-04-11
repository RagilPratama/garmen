<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKirimToko extends Model
{
    protected $table = 'barang_kirim_toko';

    protected $fillable = [
        'no_surat_jalan', 'tanggal_kirim', 'po', 'model', 'pcs_barang_jadi',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
    ];
}
