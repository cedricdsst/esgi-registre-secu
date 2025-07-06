<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batiment extends Model
{
    protected $fillable = [];
    
    public function droitsBatiment()
    {
        return $this->hasMany(DroitsBatiment::class);
    }
}