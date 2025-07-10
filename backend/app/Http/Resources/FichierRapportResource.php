<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FichierRapportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rapport_id' => $this->rapport_id,
            'observation_id' => $this->observation_id,
            'nom_original' => $this->nom_original,
            'nom_stockage' => $this->nom_stockage,
            'chemin_compresse' => $this->chemin_compresse,
            'version' => $this->version,
            'taille' => $this->taille,
            'taille_formatee' => $this->getSize(),
            'type_mime' => $this->type_mime,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations
            'rapport' => new RapportResource($this->whenLoaded('rapport')),
            'observation' => new ObservationResource($this->whenLoaded('observation')),
        ];
    }
}
