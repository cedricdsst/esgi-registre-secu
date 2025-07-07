<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
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
            'adresse' => $this->adresse,
            'code_postal' => $this->code_postal,
            'ville' => $this->ville,
            'pays' => $this->pays,
            'description' => $this->description,
            'client_id' => $this->client_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relations conditionnelles
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'nom' => $this->client->nom,
                    'prenom' => $this->client->prenom,
                    'email' => $this->client->email,
                    'organisation' => $this->client->organisation,
                ];
            }),
            
            'batiments' => BatimentResource::collection($this->whenLoaded('batiments')),
            'droits_site' => DroitsSiteResource::collection($this->whenLoaded('droitsSite')),
            
            // Métadonnées
            'stats' => $this->when($request->include_stats, function () {
                return [
                    'nombre_batiments' => $this->batiments->count(),
                    'nombre_niveaux' => $this->batiments->sum(function ($batiment) {
                        return $batiment->niveaux->count();
                    }),
                    'nombre_parties' => $this->batiments->sum(function ($batiment) {
                        return $batiment->niveaux->sum(function ($niveau) {
                            return $niveau->parties->count();
                        });
                    }),
                ];
            }),
        ];
    }
}
