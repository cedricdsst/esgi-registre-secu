<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntrepriseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'contact' => $this->contact,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'is_organisme_agree' => $this->is_organisme_agree,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
