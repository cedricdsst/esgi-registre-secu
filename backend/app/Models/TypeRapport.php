<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeRapport extends Model
{
    protected $table = 'types_rapports';
    
    protected $fillable = [
        'libelle',
        'sous_titre',
        'periodicite',
        'typologie_batiment',
        'organisme_agree_requis',
        'next_check_date'
    ];

    protected $casts = [
        'organisme_agree_requis' => 'boolean',
        'next_check_date' => 'datetime',
    ];

    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }

    public function getPeriodiciteEnMois()
    {
        return match($this->periodicite) {
            'annuelle' => 12,
            'semestrielle' => 6,
            'triennale' => 36,
            'quinquennale' => 60,
            'biannuelle' => 24,
            'ponctuelle' => null,
            default => null,
        };
    }
}
