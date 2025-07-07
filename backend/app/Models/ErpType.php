<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpType extends Model
{
    protected $fillable = [
        'tag',
        'name',
        'isSpecial'
    ];

    protected $casts = [
        'isSpecial' => 'boolean',
    ];

    public function erps()
    {
        return $this->hasMany(Erp::class);
    }
}
