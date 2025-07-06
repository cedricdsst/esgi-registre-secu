<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    protected $fillable = [];
    
    public function droitsPartie()
    {
        return $this->hasMany(DroitsPartie::class);
    }
}