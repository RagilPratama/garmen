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
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SuratJalanGarmenItem::class);
    }
}
