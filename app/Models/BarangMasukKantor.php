<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukKantor extends Model
{
    protected $table = 'barang_masuk_kantor';

    protected $fillable = [
        'no_surat_jalan', 'tanggal_kirim', 'po', 'model', 'pcs_barang_jadi', 'harga_satuan',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
    ];
}
