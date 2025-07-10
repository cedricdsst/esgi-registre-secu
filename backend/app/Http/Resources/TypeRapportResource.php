<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeRapportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'sous_titre' => $this->sous_titre,
            'periodicite' => $this->periodicite,
            'typologie_batiment' => $this->typologie_batiment,
            'organisme_agree_requis' => $this->organisme_agree_requis,
            'next_check_date' => $this->next_check_date,
            'periodicite_en_mois' => $this->getPeriodiciteEnMois(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
