<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangKirimToko extends Model
{
    protected $table = 'barang_kirim_toko';

    protected $fillable = [
        'toko_id', 'no_surat_jalan', 'tanggal_kirim', 'po', 'model', 'pcs_barang_jadi', 'nama_toko',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
