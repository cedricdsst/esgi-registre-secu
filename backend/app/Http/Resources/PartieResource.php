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
            'batiment_id' => $this->batiment_id,
            'owner_id' => $this->owner_id,
            'nom' => $this->nom,
            'type' => $this->type,
            'isICPE' => $this->isICPE,
            'isPrivative' => $this->isPrivative,
            'activites_erp' => $this->activites_erp,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relations conditionnelles
            'batiment' => $this->whenLoaded('batiment', function () {
                return [
                    'id' => $this->batiment->id,
                    'name' => $this->batiment->name,
                    'type' => $this->batiment->type,
                    'site' => $this->when($this->batiment->relationLoaded('site'), [
                        'id' => $this->batiment->site->id,
                        'nom' => $this->batiment->site->nom,
                    ]),
                ];
            }),
            
            'niveaux' => $this->whenLoaded('niveaux', function () {
                return $this->niveaux->map(function ($niveau) {
                    return [
                        'id' => $niveau->id,
                        'nom' => $niveau->nom,
                        'numero_etage' => $niveau->numero_etage,
                        'description' => $niveau->description,
                        'donnees_partie' => [
                            'libelle' => $niveau->pivot->libelle,
                            'effectif_public' => $niveau->pivot->effectif_public,
                            'personnel' => $niveau->pivot->personnel,
                            'surface_exploitation' => $niveau->pivot->surface_exploitation,
                            'surface_gla' => $niveau->pivot->surface_gla,
                            'surface_accessible_public' => $niveau->pivot->surface_accessible_public,
                        ],
                    ];
                });
            }),
            
            'lots' => LotResource::collection($this->whenLoaded('lots')),
            'droits_partie' => DroitsPartieResource::collection($this->whenLoaded('droitsPartie')),
            
            // Effectifs totaux calculés à partir des niveaux
            'effectif_public' => $this->whenLoaded('niveaux', function () {
                return $this->niveaux->sum('pivot.effectif_public') ?: 0;
            }),
            'effectif_personnel' => $this->whenLoaded('niveaux', function () {
                return $this->niveaux->sum('pivot.personnel') ?: 0;
            }),
            
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'nom' => $this->owner->nom,
                    'prenom' => $this->owner->prenom,
                    'email' => $this->owner->email,
                    'full_name' => $this->owner->full_name,
                    'organisation' => $this->owner->organisation,
                ];
            }),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_lots' => $this->lots->count(),
                    'nombre_niveaux' => $this->niveaux->count(),
                    'type_display' => $this->type === 'privative' ? 'Privative' : 'Commune',
                ];
            }),
        ];
    }
} 