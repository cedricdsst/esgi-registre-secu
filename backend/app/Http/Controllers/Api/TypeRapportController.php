<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeRapportResource;
use App\Models\TypeRapport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TypeRapportController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeRapport::query();
        
        // Filtres
        if ($request->has('typologie_batiment')) {
            $query->where('typologie_batiment', $request->typologie_batiment);
        }
        
        if ($request->has('organisme_agree_requis')) {
            $query->where('organisme_agree_requis', $request->boolean('organisme_agree_requis'));
        }

        $types = $query->orderBy('libelle')->get();
        return $types;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'sous_titre' => 'nullable|string|max:255',
            'periodicite' => 'required|in:annuelle,semestrielle,triennale,quinquennale,biannuelle,ponctuelle',
            'typologie_batiment' => 'nullable|in:ERP,IGH,HAB,BUP',
            'organisme_agree_requis' => 'boolean',
        ]);

        $types_rapport = TypeRapport::create($validated);
        return new TypeRapportResource($types_rapport);
    }

    public function show(TypeRapport $types_rapport)
    {
        
        return new TypeRapportResource($types_rapport);
    }

    public function update(Request $request, TypeRapport $types_rapport)
    {
        $validated = $request->validate([
            'libelle' => 'string|max:255',
            'sous_titre' => 'nullable|string|max:255',
            'periodicite' => 'in:annuelle,semestrielle,triennale,quinquennale,biannuelle,ponctuelle',
            'typologie_batiment' => 'nullable|in:ERP,IGH,HAB,BUP',
            'organisme_agree_requis' => 'boolean',
        ]);

        $types_rapport->update($validated);
        return new TypeRapportResource($types_rapport);
    }

    public function destroy(TypeRapport $types_rapport)
    {
        // Vérifier s'il y a des rapports liés
        if ($types_rapport->rapports()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce type de rapport car des rapports y sont liés.'
            ], Response::HTTP_CONFLICT);
        }

        $types_rapport->delete();
        return response()->json(['message' => 'Type de rapport supprimé avec succès'], Response::HTTP_OK);
    }
}
