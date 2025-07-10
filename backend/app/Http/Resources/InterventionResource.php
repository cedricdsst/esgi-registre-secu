<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterventionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'intitule' => $this->intitule,
            'entreprise_nom' => $this->entreprise_nom,
            'intervenant_nom' => $this->intervenant_nom,
            'type_intervention_id' => $this->type_intervention_id,
            'statut' => $this->statut,
            'signed_at' => $this->signed_at,
            'signed_by' => $this->signed_by,
            'is_signed' => $this->isSigned(),
            'can_be_completed' => $this->canBeCompleted(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations
            'type_intervention' => new TypeInterventionResource($this->whenLoaded('typeIntervention')),
            'parties' => PartieResource::collection($this->whenLoaded('parties')),
            'rapports' => RapportResource::collection($this->whenLoaded('rapports')),
            'observations_suivi' => ObservationResource::collection($this->whenLoaded('observationsSuivi')),
        ];
    }
}
