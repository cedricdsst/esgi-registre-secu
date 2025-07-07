<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'niveau',
        'type'
    ];

    protected $casts = [
        'niveau' => 'integer',
    ];

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'partie_lot');
    }
}
