<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BahanProsesPotong extends Model
{
    protected $table = 'bahan_proses_potong';

    protected $fillable = [
        'tanggal_potong', 'yard', 'po', 'model', 'kode_bahan', 'hasil_potongan',
    ];

    protected $casts = [
        'tanggal_potong' => 'date',
    ];

    public function bahanMasuk(): HasOne
    {
        return $this->hasOne(BahanMasuk::class, 'kode_bahan', 'kode_bahan');
    }
}
