<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionSuiviObservation extends Model
{
    protected $table = 'intervention_suivi_observation';
    
    protected $fillable = [
        'intervention_id',
        'observation_id'
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }
}
