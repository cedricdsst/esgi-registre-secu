<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rapport_id',
        'identification',
        'libelle',
        'localisation',
        'priorite',
        'statut_traitement',
        'deja_signalee',
        'date_signalement_precedent'
    ];

    protected $casts = [
        'deja_signalee' => 'boolean',
        'date_signalement_precedent' => 'date',
    ];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'observation_partie')
                    ->withTimestamps();
    }

    public function fichiers()
    {
        return $this->hasMany(FichierRapport::class);
    }

    public function interventionsSuivi()
    {
        return $this->belongsToMany(Intervention::class, 'intervention_suivi_observation')
                    ->withTimestamps();
    }

    public function needsFollowUp()
    {
        return in_array($this->statut_traitement, ['nouveau', 'en_cours', 'reporte']);
    }
}
