<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeInterventionResource;
use App\Models\TypeIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TypeInterventionController extends Controller
{
    public function index()
    {
        $types = TypeIntervention::orderBy('ordre_priorite')->get();
        return TypeInterventionResource::collection($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'ordre_priorite' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $typeIntervention = TypeIntervention::create($validated);
        return new TypeInterventionResource($typeIntervention);
    }

    public function show(TypeIntervention $typeIntervention)
    {
        return new TypeInterventionResource($typeIntervention);
    }

    public function update(Request $request, TypeIntervention $typeIntervention)
    {
        $validated = $request->validate([
            'nom' => 'string|max:255',
            'ordre_priorite' => 'integer|min:1',
            'description' => 'nullable|string',
        ]);

        $typeIntervention->update($validated);
        return new TypeInterventionResource($typeIntervention);
    }

    public function destroy(TypeIntervention $typeIntervention)
    {
        // Vérifier s'il y a des interventions liées
        if ($typeIntervention->interventions()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce type d\'intervention car des interventions y sont liées.'
            ], Response::HTTP_CONFLICT);
        }

        $typeIntervention->delete();
        return response()->json(['message' => 'Type d\'intervention supprimé avec succès'], Response::HTTP_OK);
    }
}
