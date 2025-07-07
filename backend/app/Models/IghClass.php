<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IghClass extends Model
{
    protected $fillable = [
        'tag',
        'name'
    ];

    public function ighs()
    {
        return $this->hasMany(Igh::class);
    }
}
