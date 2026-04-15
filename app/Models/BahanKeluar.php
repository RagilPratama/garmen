<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BahanKeluar extends Model
{
    protected $table = 'bahan_keluar';

    protected $fillable = [
        'tanggal', 'no_surat_jalan', 'kode_bahan', 'yard', 'rp_per_yard', 'total',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function bahanMasuk(): HasOne
    {
        return $this->hasOne(BahanMasuk::class, 'kode_bahan', 'kode_bahan');
    }
}
