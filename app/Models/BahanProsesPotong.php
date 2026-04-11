<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanProsesPotong extends Model
{
    protected $table = 'bahan_proses_potong';

    protected $fillable = [
        'tanggal_potong', 'yard', 'po', 'model', 'kode_bahan', 'hasil_potongan',
    ];

    protected $casts = [
        'tanggal_potong' => 'date',
    ];
}
