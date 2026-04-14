<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    protected $table = 'defects';

    protected $fillable = [
        'sumber',
        'po',
        'model',
        'pcs_defect',
        'keterangan',
        'referensi_id',
    ];
}
