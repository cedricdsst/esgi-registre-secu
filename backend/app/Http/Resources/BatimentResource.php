<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatimentResource extends JsonResource
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
            'site_id' => $this->site_id,
            'name' => $this->name,
            'type' => $this->type,
            'isICPE' => $this->isICPE,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relations conditionnelles
            'site' => $this->whenLoaded('site', function () {
                return [
                    'id' => $this->site->id,
                    'nom' => $this->site->nom,
                    'adresse' => $this->site->adresse,
                    'ville' => $this->site->ville,
                ];
            }),
            
            'niveaux' => NiveauResource::collection($this->whenLoaded('niveaux')),
            'parties' => PartieResource::collection($this->whenLoaded('parties')),
            'droits_batiment' => DroitsBatimentResource::collection($this->whenLoaded('droitsBatiment')),
            
            // Typologies spécifiques
            'erps' => ErpResource::collection($this->whenLoaded('erps')),
            'ighs' => IghResource::collection($this->whenLoaded('ighs')),
            'habs' => HabResource::collection($this->whenLoaded('habs')),
            'bups' => BupResource::collection($this->whenLoaded('bups')),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_niveaux' => $this->niveaux->count(),
                    'nombre_parties' => $this->niveaux->sum(function ($niveau) {
                        return $niveau->parties->count();
                    }),
                    'typologie_details' => [
                        'erps_count' => $this->erps->count(),
                        'ighs_count' => $this->ighs->count(),
                        'habs_count' => $this->habs->count(),
                        'bups_count' => $this->bups->count(),
                    ],
                ];
            }),
        ];
    }
} 