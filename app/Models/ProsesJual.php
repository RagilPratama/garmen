<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProsesJual extends Model
{
    protected $table = 'proses_jual';

    protected $fillable = [
        'toko_id', 'no_nota', 'tanggal_nota', 'buyer', 'model',
        'pcs', 'harga_satuan', 'diskon', 'total_harga', 'status', 'bayar',
    ];

    protected $casts = [
        'tanggal_nota' => 'date',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
