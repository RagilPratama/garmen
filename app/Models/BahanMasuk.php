<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanMasuk extends Model
{
    protected $table = 'bahan_masuk';

    protected $fillable = [
        'tanggal', 'no_surat_jalan', 'no_nota', 'supplier',
        'kode_bahan', 'yard', 'rp_per_yard', 'total', 'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
