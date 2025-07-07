<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DroitsNiveauResource extends JsonResource
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
            'niveau_id' => $this->niveau_id,
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
            
            'niveau' => $this->whenLoaded('niveau', function () {
                return [
                    'id' => $this->niveau->id,
                    'nom' => $this->niveau->nom,
                    'numero_etage' => $this->niveau->numero_etage,
                    'batiment' => $this->when($this->niveau->relationLoaded('batiment'), [
                        'id' => $this->niveau->batiment->id,
                        'name' => $this->niveau->batiment->name,
                        'type' => $this->niveau->batiment->type,
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