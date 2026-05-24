<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BahanKeluar extends Model
{
    protected $table = 'bahan_keluar';

    protected $fillable = ['tanggal', 'no_surat_jalan', 'kode_bahan', 'quantity', 'satuan', 'harga_satuan', 'total_harga'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function bahanMasuk(): HasOne
    {
        return $this->hasOne(BahanMasuk::class, 'kode_bahan', 'kode_bahan');
    }
}
