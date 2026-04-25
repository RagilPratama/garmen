<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasTransfer extends Model
{
    protected $table = 'kas_transfer';

    protected $fillable = [
        'toko_pengirim_id',
        'toko_penerima_id',
        'tanggal',
        'metode_bayar',
        'jumlah',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function tokoPengirim(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_pengirim_id');
    }

    public function tokoPenerima(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_penerima_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
