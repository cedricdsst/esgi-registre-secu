<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rapport_id' => $this->rapport_id,
            'identification' => $this->identification,
            'libelle' => $this->libelle,
            'localisation' => $this->localisation,
            'priorite' => $this->priorite,
            'statut_traitement' => $this->statut_traitement,
            'deja_signalee' => $this->deja_signalee,
            'date_signalement_precedent' => $this->date_signalement_precedent,
            'needs_follow_up' => $this->needsFollowUp(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations
            'rapport' => new RapportResource($this->whenLoaded('rapport')),
            'parties' => PartieResource::collection($this->whenLoaded('parties')),
            'fichiers' => FichierRapportResource::collection($this->whenLoaded('fichiers')),
            'interventions_suivi' => InterventionResource::collection($this->whenLoaded('interventionsSuivi')),
        ];
    }
}
