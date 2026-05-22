<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanGarmen extends Model
{
    protected $table = 'surat_jalan_garmen';

    protected $fillable = [
        'no_surat_jalan',
        'tanggal',
        'keterangan',
        'marker_approved',
        'pola_approved',
        'superadmin_allow_print',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'marker_approved' => 'boolean',
        'pola_approved' => 'boolean',
        'superadmin_allow_print' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(SuratJalanGarmenItem::class);
    }
}
