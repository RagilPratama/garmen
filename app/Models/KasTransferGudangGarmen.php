<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasTransferGudangGarmen extends Model
{
    protected $table = 'kas_transfer_gudang_garmen';

    protected $fillable = [
        'entitas_pengirim',
        'entitas_penerima',
        'tanggal',
        'metode_bayar',
        'jumlah',
        'keterangan',
        'rekening_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
