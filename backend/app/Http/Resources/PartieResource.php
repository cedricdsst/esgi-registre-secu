<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'niveau_id' => $this->niveau_id,
            'nom' => $this->nom,
            'type' => $this->type,
            'isICPE' => $this->isICPE,
            'isPrivative' => $this->isPrivative,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relations conditionnelles
            'niveau' => $this->whenLoaded('niveau', function () {
                return [
                    'id' => $this->niveau->id,
                    'nom' => $this->niveau->nom,
                    'numero_etage' => $this->niveau->numero_etage,
                    'batiment' => $this->when($this->niveau->relationLoaded('batiment'), [
                        'id' => $this->niveau->batiment->id,
                        'name' => $this->niveau->batiment->name,
                        'type' => $this->niveau->batiment->type,
                        'site' => $this->when($this->niveau->batiment->relationLoaded('site'), [
                            'id' => $this->niveau->batiment->site->id,
                            'nom' => $this->niveau->batiment->site->nom,
                        ]),
                    ]),
                ];
            }),
            
            'lots' => LotResource::collection($this->whenLoaded('lots')),
            'droits_partie' => DroitsPartieResource::collection($this->whenLoaded('droitsPartie')),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_lots' => $this->lots->count(),
                    'type_display' => $this->type === 'privative' ? 'Privative' : 'Commune',
                ];
            }),
        ];
    }
} 