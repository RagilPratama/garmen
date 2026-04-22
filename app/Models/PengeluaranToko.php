<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengeluaranToko extends Model
{
    protected $table = 'pengeluaran_toko';

    protected $fillable = [
        'toko_id',
        'tanggal',
        'kategori',
        'keterangan',
        'jumlah',
        'metode_bayar',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
