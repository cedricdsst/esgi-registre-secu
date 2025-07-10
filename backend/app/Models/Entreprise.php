<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'contact',
        'telephone',
        'email',
        'is_organisme_agree'
    ];

    protected $casts = [
        'is_organisme_agree' => 'boolean',
    ];

    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'entreprise_nom', 'nom');
    }
}
