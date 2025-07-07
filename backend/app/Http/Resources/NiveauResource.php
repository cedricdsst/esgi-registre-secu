<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NiveauResource extends JsonResource
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
            'nom' => $this->nom,
            'numero_etage' => $this->numero_etage,
            'description' => $this->description,
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
            
            'parties' => PartieResource::collection($this->whenLoaded('parties')),
            'droits_niveau' => DroitsNiveauResource::collection($this->whenLoaded('droitsNiveau')),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_parties' => $this->parties->count(),
                    'parties_privatives' => $this->parties->where('isPrivative', true)->count(),
                    'parties_communes' => $this->parties->where('isPrivative', false)->count(),
                ];
            }),
        ];
    }
} 