<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DroitsPartie extends Model
{
    protected $table = 'droits_partie';
    
    protected $fillable = [
        'utilisateur_id', 
        'partie_id', 
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

    public function partie()
    {
        return $this->belongsTo(Partie::class);
    }
}