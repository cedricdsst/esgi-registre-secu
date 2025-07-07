<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partie extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'niveau_id',
        'nom',
        'type',
        'isICPE',
        'isPrivative'
    ];

    protected $casts = [
        'isICPE' => 'boolean',
        'isPrivative' => 'boolean',
        'createdAt' => 'datetime',
        'modifiedAt' => 'datetime',
    ];
    
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function droitsPartie()
    {
        return $this->hasMany(DroitsPartie::class);
    }

    public function lots()
    {
        return $this->belongsToMany(Lot::class, 'partie_lot')
                    ->withPivot('libelle', 'type')
                    ->withTimestamps();
    }
}