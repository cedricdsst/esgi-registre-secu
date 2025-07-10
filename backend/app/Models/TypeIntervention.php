<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeIntervention extends Model
{
    protected $table = 'types_interventions';
    
    protected $fillable = [
        'nom',
        'ordre_priorite',
        'description'
    ];

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }
}
