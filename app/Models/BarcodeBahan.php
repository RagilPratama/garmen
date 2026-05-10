<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeBahan extends Model
{
    protected $table = 'barcode_bahan';
    
    protected $fillable = [
        'barcode_code',
        'no_surat_jalan',
        'supplier',
        'kode_bahan',
        'nama_bahan',
        'quantity',
        'satuan',
        'rp_per_yard',
        'total_harga',
        'tanggal',
        'harga_sudah_diisi'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rp_per_yard' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'tanggal' => 'date',
        'harga_sudah_diisi' => 'boolean'
    ];
}
