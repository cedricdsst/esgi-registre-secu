<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
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
            'nom' => $this->nom,
            'niveau' => $this->niveau,
            'type' => $this->type,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Données pivot si disponibles
            'pivot' => $this->when($this->pivot, [
                'libelle' => $this->pivot?->libelle ?? null,
                'type' => $this->pivot?->type ?? null,
                'created_at' => $this->pivot?->created_at?->toISOString(),
                'updated_at' => $this->pivot?->updated_at?->toISOString(),
            ]),
            
            // Relations conditionnelles
            'parties' => $this->when($this->relationLoaded('parties'), function () {
                return $this->parties->map(function ($partie) {
                    return [
                        'id' => $partie->id,
                        'nom' => $partie->nom,
                        'type' => $partie->type,
                        'pivot' => [
                            'libelle' => $partie->pivot?->libelle ?? null,
                            'type' => $partie->pivot?->type ?? null,
                        ],
                    ];
                });
            }),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_parties' => $this->parties->count(),
                ];
            }),
        ];
    }
} 