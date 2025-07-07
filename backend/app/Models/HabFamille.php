<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabFamille extends Model
{
    protected $fillable = [
        'name'
    ];

    public function habs()
    {
        return $this->hasMany(Hab::class);
    }
}
