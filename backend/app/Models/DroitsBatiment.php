<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DroitsBatiment extends Model
{
    protected $table = 'droits_batiment';
    
    protected $fillable = [
        'utilisateur_id', 
        'batiment_id', 
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

    public function batiment()
    {
        return $this->belongsTo(Batiment::class, 'batiment_id');
    }
}