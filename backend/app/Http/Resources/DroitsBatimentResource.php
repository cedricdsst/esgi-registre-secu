<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DroitsBatimentResource extends JsonResource
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
            'batiment_id' => $this->batiment_id,
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
            
            // Métadonnées
            'permissions' => [
                'can_read' => $this->lecture,
                'can_write' => $this->ecriture,
                'permission_level' => $this->ecriture ? 'Écriture' : ($this->lecture ? 'Lecture' : 'Aucun'),
            ],
        ];
    }
} 