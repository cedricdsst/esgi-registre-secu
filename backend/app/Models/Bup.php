<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bup extends Model
{
    protected $fillable = [
        'name',
        'batiment_id'
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }
}
