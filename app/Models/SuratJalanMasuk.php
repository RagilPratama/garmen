<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanMasuk extends Model
{
    protected $table = 'surat_jalan_masuk';

    protected $fillable = [
        'no_surat_jalan',
        'no_nota',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
