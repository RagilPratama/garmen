<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanKeluar extends Model
{
    protected $table = 'bahan_keluar';

    protected $fillable = [
        'tanggal', 'no_surat_jalan', 'kode_bahan', 'yard',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
