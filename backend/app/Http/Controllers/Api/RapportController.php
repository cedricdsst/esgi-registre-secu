<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RapportResource;
use App\Models\Rapport;
use App\Models\Intervention;
use App\Models\TypeRapport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $query = Rapport::with(['intervention', 'typeRapport', 'parties']);
        
        // Filtres
        if ($request->has('intervention_id')) {
            $query->where('intervention_id', $request->intervention_id);
        }
        
        if ($request->has('type_rapport_id')) {
            $query->where('type_rapport_id', $request->type_rapport_id);
        }
        
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('partie_id')) {
            $query->whereHas('parties', function ($q) use ($request) {
                $q->where('parties.id', $request->partie_id);
            });
        }

        $rapports = $query->orderBy('date_emission', 'desc')->get();
        return $rapports;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'intervention_id' => 'required|exists:interventions,id',
            'type_rapport_id' => 'required|exists:types_rapports,id',
            'date_emission' => 'required|date',
            'equipements_selection' => 'nullable|array',
            'equipements_selection.*' => 'integer', // IDs des équipements de l'API
            'partie_ids' => 'required|array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $rapport = Rapport::create($validated);
        
        // Attacher les parties
        $rapport->parties()->attach($validated['partie_ids']);
        
        $rapport->load(['intervention', 'typeRapport', 'parties']);
        return new RapportResource($rapport);
    }

    public function show(Rapport $rapport)
    {
        $rapport->load(['intervention', 'typeRapport', 'parties', 'observations', 'fichiers']);
        return new RapportResource($rapport);
    }

    public function update(Request $request, Rapport $rapport)
    {
        $validated = $request->validate([
            'date_emission' => 'date',
            'statut' => 'in:brouillon,finalise,signe,archive',
            'equipements_selection' => 'nullable|array',
            'equipements_selection.*' => 'integer',
            'partie_ids' => 'array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $rapport->update($validated);
        
        // Mettre à jour les parties si fourni
        if (isset($validated['partie_ids'])) {
            $rapport->parties()->sync($validated['partie_ids']);
        }
        
        $rapport->load(['intervention', 'typeRapport', 'parties', 'observations', 'fichiers']);
        return new RapportResource($rapport);
    }

    public function destroy(Rapport $rapport)
    {
        $rapport->delete();
        return response()->json(['message' => 'Rapport supprimé avec succès'], Response::HTTP_OK);
    }

    public function sign(Request $request, Rapport $rapport)
    {
        if ($rapport->statut !== 'finalise') {
            return response()->json([
                'message' => 'Le rapport doit être finalisé avant d\'être signé.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rapport->update(['statut' => 'signe']);
        
        $rapport->load(['intervention', 'typeRapport', 'parties', 'observations', 'fichiers']);
        return new RapportResource($rapport);
    }

    public function archive(Request $request, Rapport $rapport)
    {
        if ($rapport->statut !== 'signe') {
            return response()->json([
                'message' => 'Le rapport doit être signé avant d\'être archivé.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rapport->update(['statut' => 'archive']);
        
        $rapport->load(['intervention', 'typeRapport', 'parties', 'observations', 'fichiers']);
        return new RapportResource($rapport);
    }
}
