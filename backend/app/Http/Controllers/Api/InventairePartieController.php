<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventairePartieResource;
use App\Models\InventairePartie;
use App\Models\Partie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventairePartieController extends Controller
{
    public function index(Request $request)
    {
        $query = InventairePartie::with(['partie']);
        
        // Filtres
        if ($request->has('partie_id')) {
            $query->where('partie_id', $request->partie_id);
        }
        
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $inventaires = $query->orderBy('localisation')->get();
        return $inventaires;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partie_id' => 'required|exists:parties,id',
            'product_id' => 'required|integer',
            'localisation' => 'required|string|max:255',
            'quantite' => 'required|integer|min:1',
            'donnees_produit' => 'nullable|array', // Cache des données de l'API
        ]);

        $inventaire = InventairePartie::create($validated);
        
        $inventaire->load(['partie']);
        return new InventairePartieResource($inventaire);
    }

    public function show(InventairePartie $inventairePartie)
    {
        $inventairePartie->load(['partie']);
        return new InventairePartieResource($inventairePartie);
    }

    public function update(Request $request, InventairePartie $inventairePartie)
    {
        $validated = $request->validate([
            'localisation' => 'string|max:255',
            'quantite' => 'integer|min:1',
            'donnees_produit' => 'nullable|array',
        ]);

        $inventairePartie->update($validated);
        
        $inventairePartie->load(['partie']);
        return new InventairePartieResource($inventairePartie);
    }

    public function destroy(InventairePartie $inventairePartie)
    {
        $inventairePartie->delete();
        return response()->json(['message' => 'Inventaire supprimé avec succès'], Response::HTTP_OK);
    }

    public function getByPartie(Partie $partie)
    {
        $inventaires = $partie->inventaires()->get();
        return InventairePartieResource::collection($inventaires);
    }

    public function syncWithApi(InventairePartie $inventairePartie)
    {
        // Cette méthode sera implémentée pour synchroniser avec l'API externe
        // Pour l'instant, on retourne l'inventaire tel quel
        
        // TODO: Implémenter la synchronisation avec l'API des équipements
        // $productData = $this->fetchProductFromApi($inventairePartie->product_id);
        // $inventairePartie->update(['donnees_produit' => $productData]);
        
        $inventairePartie->load(['partie']);
        return new InventairePartieResource($inventairePartie);
    }

    // Méthode privée pour récupérer les données depuis l'API (à implémenter)
    private function fetchProductFromApi($productId)
    {
        // TODO: Implémenter l'appel à l'API externe
        // return Http::get("api/products/{$productId}")->json();
        return null;
    }
}
