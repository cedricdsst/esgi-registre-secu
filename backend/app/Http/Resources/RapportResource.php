<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RapportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'intervention_id' => $this->intervention_id,
            'type_rapport_id' => $this->type_rapport_id,
            'date_emission' => $this->date_emission,
            'statut' => $this->statut,
            'equipements_selection' => $this->equipements_selection,
            'is_signed' => $this->isSigned(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations
            'intervention' => new InterventionResource($this->whenLoaded('intervention')),
            'type_rapport' => new TypeRapportResource($this->whenLoaded('typeRapport')),
            'parties' => PartieResource::collection($this->whenLoaded('parties')),
            'observations' => ObservationResource::collection($this->whenLoaded('observations')),
            'fichiers' => FichierRapportResource::collection($this->whenLoaded('fichiers')),
        ];
    }
}
