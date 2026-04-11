<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesCuci extends Model
{
    protected $table = 'proses_cuci';

    protected $fillable = [
        'tanggal_kirim_cuci', 'no_surat_jalan', 'po', 'model',
        'pcs_kirim', 'tanggal_kembali_dari_cuci', 'pcs_kembali',
    ];

    protected $casts = [
        'tanggal_kirim_cuci' => 'date',
        'tanggal_kembali_dari_cuci' => 'date',
    ];
}
