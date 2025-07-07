<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hab extends Model
{
    protected $fillable = [
        'name',
        'batiment_id',
        'hab_famille_id'
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function habFamille()
    {
        return $this->belongsTo(HabFamille::class);
    }
}
