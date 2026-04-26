<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasGudang extends Model
{
    protected $table = 'kas_gudang';

    protected $fillable = [
        'tanggal',
        'jenis',
        'metode_bayar',
        'rekening_id',
        'kategori',
        'jumlah',
        'saldo_sesudah',
        'keterangan',
        'referensi',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }
}
