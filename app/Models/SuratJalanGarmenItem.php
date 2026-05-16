<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanGarmenItem extends Model
{
    protected $table = 'surat_jalan_garmen_items';

    protected $fillable = [
        'surat_jalan_garmen_id',
        'barcode_bahan_id',
        'kode_bahan',
        'nama_bahan',
        'supplier',
        'quantity',
        'satuan',
        'harga_keluar',
        'total_harga',
    ];

    protected $casts = [
        'quantity' => 'float',
        'harga_keluar' => 'float',
        'total_harga' => 'float',
    ];

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalanGarmen::class, 'surat_jalan_garmen_id');
    }

    public function barcodeBahan()
    {
        return $this->belongsTo(BarcodeBahan::class, 'barcode_bahan_id');
    }
}
