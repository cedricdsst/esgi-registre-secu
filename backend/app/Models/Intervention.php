<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervention extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intitule',
        'entreprise_nom',
        'intervenant_nom',
        'type_intervention_id',
        'statut',
        'signed_at',
        'signed_by',
        'created_by'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function typeIntervention()
    {
        return $this->belongsTo(TypeIntervention::class);
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'intervention_partie')
                    ->withTimestamps();
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }

    public function observationsSuivi()
    {
        return $this->belongsToMany(Observation::class, 'intervention_suivi_observation')
                    ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSigned()
    {
        return !is_null($this->signed_at);
    }

    public function canBeCompleted()
    {
        return $this->statut === 'en_cours';
    }
}
