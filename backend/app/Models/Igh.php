<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Igh extends Model
{
    protected $fillable = [
        'name',
        'batiment_id',
        'igh_classe_id'
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function ighClass()
    {
        return $this->belongsTo(IghClass::class, 'igh_classe_id');
    }
}
