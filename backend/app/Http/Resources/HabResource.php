<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HabResource extends JsonResource
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
            'name' => $this->name,
            'batiment_id' => $this->batiment_id,
            'hab_famille_id' => $this->hab_famille_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relations conditionnelles
            'batiment' => $this->whenLoaded('batiment', function () {
                return [
                    'id' => $this->batiment->id,
                    'name' => $this->batiment->name,
                    'type' => $this->batiment->type,
                ];
            }),
            
            'hab_famille' => $this->whenLoaded('habFamille', function () {
                return [
                    'id' => $this->habFamille->id,
                    'name' => $this->habFamille->name,
                ];
            }),
            
            // Métadonnées
            'display_info' => [
                'type_display' => 'HAB',
                'full_name' => $this->name . ' (HAB)',
                'famille_display' => $this->habFamille ? "Famille {$this->habFamille->name}" : null,
            ],
        ];
    }
} 