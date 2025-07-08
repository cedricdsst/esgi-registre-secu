<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batiment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'type',
        'isICPE'
    ];

    protected $casts = [
        'isICPE' => 'boolean',
        'createdAt' => 'datetime',
        'modifiedAt' => 'datetime',
    ];
    
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function droitsBatiment()
    {
        return $this->hasMany(DroitsBatiment::class);
    }

    public function niveaux()
    {
        return $this->hasMany(Niveau::class);
    }
    
    public function parties()
    {
        return $this->hasMany(Partie::class);
    }

    public function erps()
    {
        return $this->hasMany(Erp::class);
    }

    public function ighs()
    {
        return $this->hasMany(Igh::class);
    }

    public function habs()
    {
        return $this->hasMany(Hab::class);
    }

    public function bups()
    {
        return $this->hasMany(Bup::class);
    }
}