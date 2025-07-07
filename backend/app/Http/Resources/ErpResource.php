<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErpResource extends JsonResource
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
            'erp_categorie' => $this->erp_categorie,
            'erp_type_id' => $this->erp_type_id,
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
            
            'erp_type' => $this->whenLoaded('erpType', function () {
                return [
                    'id' => $this->erpType->id,
                    'tag' => $this->erpType->tag,
                    'name' => $this->erpType->name,
                    'isSpecial' => $this->erpType->isSpecial,
                ];
            }),
            
            // Métadonnées
            'display_info' => [
                'type_display' => 'ERP',
                'full_name' => $this->name . ' (ERP)',
                'category_display' => $this->erp_categorie ? "Catégorie {$this->erp_categorie}" : null,
            ],
        ];
    }
} 