<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Niveau extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batiment_id',
        'nom',
        'numero_etage',
        'description'
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'partie_niveau')
                    ->withPivot([
                        'libelle',
                        'effectif_public',
                        'personnel',
                        'surface_exploitation',
                        'surface_gla',
                        'surface_accessible_public'
                    ])
                    ->withTimestamps();
    }

    public function droitsNiveau()
    {
        return $this->hasMany(DroitsNiveau::class);
    }
} 