<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Erp extends Model
{
    protected $fillable = [
        'name',
        'batiment_id',
        'erp_categorie',
        'erp_type_id'
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function erpType()
    {
        return $this->belongsTo(ErpType::class);
    }
}
