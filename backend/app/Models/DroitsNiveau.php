<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DroitsNiveau extends Model
{
    protected $table = 'droits_niveau';
    
    protected $fillable = [
        'utilisateur_id', 
        'niveau_id', 
        'lecture', 
        'ecriture'
    ];

    protected $casts = [
        'lecture' => 'boolean',
        'ecriture' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }
} 