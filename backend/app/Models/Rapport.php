<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rapport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intervention_id',
        'type_rapport_id',
        'date_emission',
        'statut',
        'equipements_selection'
    ];

    protected $casts = [
        'date_emission' => 'date',
        'equipements_selection' => 'array',
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function typeRapport()
    {
        return $this->belongsTo(TypeRapport::class);
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'rapport_partie')
                    ->withTimestamps();
    }

    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    public function fichiers()
    {
        return $this->hasMany(FichierRapport::class);
    }

    public function isSigned()
    {
        return $this->statut === 'signe';
    }
}
