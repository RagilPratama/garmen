<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesJahit extends Model
{
    protected $table = 'proses_jahit';

    protected $fillable = [
        'tanggal_jahit', 'po', 'model', 'pcs_potongan',
        'tanggal_selesai_jahit', 'pcs_hasil_jahit',
    ];

    protected $casts = [
        'tanggal_jahit' => 'date',
        'tanggal_selesai_jahit' => 'date',
    ];
}
