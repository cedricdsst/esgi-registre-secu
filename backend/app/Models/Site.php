<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'description',
        'client_id'
    ];

    public function batiments()
    {
        return $this->hasMany(Batiment::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function droitsSite()
    {
        return $this->hasMany(DroitsSite::class);
    }
} 