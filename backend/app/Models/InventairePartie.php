<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventairePartie extends Model
{
    protected $table = 'inventaires_partie';
    
    protected $fillable = [
        'partie_id',
        'product_id',
        'localisation',
        'donnees_produit',
        'quantite'
    ];

    protected $casts = [
        'donnees_produit' => 'array',
    ];

    public function partie()
    {
        return $this->belongsTo(Partie::class);
    }

    public function getProductFromApi()
    {
        // Cette méthode sera implémentée avec l'intégration API
        return $this->donnees_produit;
    }

    public function syncWithApi()
    {
        // Cette méthode sera implémentée pour synchroniser avec l'API externe
        // Récupère les données fraîches et met à jour le cache
    }
}
