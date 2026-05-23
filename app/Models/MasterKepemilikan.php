<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_kepemilikan', 'keterangan'])]
class MasterKepemilikan extends Model
{
    protected $table = 'master_kepemilikans';

    public function barcodeBahan()
    {
        return $this->hasMany(BarcodeBahan::class, 'kepemilikan_id');
    }
}
