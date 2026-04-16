<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanPembayaran extends Model
{
    protected $table = 'penjualan_pembayaran';

    protected $fillable = [
        'no_nota', 'channel', 'tanggal_bayar', 'jumlah', 'metode', 'rekening_id', 'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah'        => 'float',
    ];
}
