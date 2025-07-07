<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IghResource extends JsonResource
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
            'igh_classe_id' => $this->igh_classe_id,
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
            
            'igh_class' => $this->whenLoaded('ighClass', function () {
                return [
                    'id' => $this->ighClass->id,
                    'tag' => $this->ighClass->tag,
                    'name' => $this->ighClass->name,
                ];
            }),
            
            // Métadonnées
            'display_info' => [
                'type_display' => 'IGH',
                'full_name' => $this->name . ' (IGH)',
                'class_display' => $this->ighClass ? "Classe {$this->ighClass->tag}" : null,
            ],
        ];
    }
} 