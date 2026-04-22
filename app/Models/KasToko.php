<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasToko extends Model
{
    protected $table = 'kas_toko';

    protected $fillable = [
        'toko_id',
        'tanggal',
        'jenis',
        'metode_bayar',
        'kategori',
        'jumlah',
        'keterangan',
        'referensi',
        'saldo_sebelum',
        'saldo_sesudah',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
