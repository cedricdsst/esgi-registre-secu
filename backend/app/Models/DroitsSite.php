<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DroitsSite extends Model
{
    protected $table = 'droits_site';
    
    protected $fillable = [
        'utilisateur_id', 
        'site_id', 
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

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
} 