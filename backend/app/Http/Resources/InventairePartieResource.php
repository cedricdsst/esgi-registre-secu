<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventairePartieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partie_id' => $this->partie_id,
            'product_id' => $this->product_id,
            'localisation' => $this->localisation,
            'donnees_produit' => $this->donnees_produit,
            'quantite' => $this->quantite,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations
            'partie' => new PartieResource($this->whenLoaded('partie')),
        ];
    }
}
