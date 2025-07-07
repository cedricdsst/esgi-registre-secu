<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DroitsPartieResource extends JsonResource
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
            'utilisateur_id' => $this->utilisateur_id,
            'partie_id' => $this->partie_id,
            'lecture' => $this->lecture,
            'ecriture' => $this->ecriture,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relations conditionnelles
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'nom' => $this->user->nom,
                    'prenom' => $this->user->prenom,
                    'email' => $this->user->email,
                    'organisation' => $this->user->organisation,
                ];
            }),
            
            'partie' => $this->whenLoaded('partie', function () {
                return [
                    'id' => $this->partie->id,
                    'nom' => $this->partie->nom,
                    'type' => $this->partie->type,
                    'isPrivative' => $this->partie->isPrivative,
                    'niveau' => $this->when($this->partie->relationLoaded('niveau'), [
                        'id' => $this->partie->niveau->id,
                        'nom' => $this->partie->niveau->nom,
                        'numero_etage' => $this->partie->niveau->numero_etage,
                    ]),
                ];
            }),
            
            // Métadonnées
            'permissions' => [
                'can_read' => $this->lecture,
                'can_write' => $this->ecriture,
                'permission_level' => $this->ecriture ? 'Écriture' : ($this->lecture ? 'Lecture' : 'Aucun'),
            ],
        ];
    }
} 