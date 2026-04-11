<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanMasukPembayaran extends Model
{
    protected $table = 'bahan_masuk_pembayaran';

    protected $fillable = [
        'no_nota',
        'tanggal_bayar',
        'jumlah',
        'metode',
        'rekening_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date:Y-m-d',
        'jumlah'        => 'decimal:2',
    ];
}
