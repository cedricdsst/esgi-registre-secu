<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InterventionResource;
use App\Models\Intervention;
use App\Models\TypeIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionController extends Controller
{
    public function index(Request $request)
{
    $query = Intervention::with(['typeIntervention', 'parties']);
    
    // Filtres
    if ($request->has('statut')) {
        $query->where('statut', $request->statut);
    }
    
    if ($request->query('type_intervention_id')) {
        $query->where('type_intervention_id', $request->query('type_intervention_id'));
    }
    
    if ($request->has('partie_id')) {
        $query->whereHas('parties', function ($q) use ($request) {
            $q->where('parties.id', $request->partie_id);
        });
    }

        $interventions = $query->orderBy('created_at', 'desc')->get();
        
        // Charger les observations de suivi pour les interventions de type "Suivi d'observation"
        $interventions->load(['observationsSuivi' => function ($query) {
            $query->with('rapport');
        }]);
        
        return $interventions;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'intitule' => 'required|string|max:255',
            'entreprise_nom' => 'required|string|max:255',
            'intervenant_nom' => 'required|string|max:255',
            'type_intervention_id' => 'required|exists:types_interventions,id',
            'partie_ids' => 'required|array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $intervention = Intervention::create($validated);
        
        // Attacher les parties
        $intervention->parties()->attach($validated['partie_ids']);
        
        $intervention->load(['typeIntervention', 'parties']);
        return new InterventionResource($intervention);
    }

    public function show(Intervention $intervention)
    {
        // Charger les observations de suivi seulement pour les interventions de type "Suivi d'observation"
        $relations = ['typeIntervention', 'parties', 'rapports'];
        
        if ($intervention->typeIntervention && $intervention->typeIntervention->nom === 'Suivi d\'observation') {
            $relations[] = 'observationsSuivi';
        }
        
        $intervention->load($relations);
        return new InterventionResource($intervention);
    }

    public function update(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'intitule' => 'string|max:255',
            'entreprise_nom' => 'string|max:255',
            'intervenant_nom' => 'string|max:255',
            'type_intervention_id' => 'exists:types_interventions,id',
            'statut' => 'in:planifie,en_cours,termine,annule',
            'partie_ids' => 'array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $intervention->update($validated);
        
        // Mettre à jour les parties si fourni
        if (isset($validated['partie_ids'])) {
            $intervention->parties()->sync($validated['partie_ids']);
        }
        
        $intervention->load(['typeIntervention', 'parties', 'rapports']);
        return new InterventionResource($intervention);
    }

    public function destroy(Intervention $intervention)
    {
        $intervention->delete();
        return response()->json(['message' => 'Intervention supprimée avec succès'], Response::HTTP_OK);
    }

    public function sign(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'signed_by' => 'required|string|max:255',
        ]);

        if ($intervention->statut !== 'termine') {
            return response()->json([
                'message' => 'L\'intervention doit être terminée avant d\'être signée.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $intervention->update([
            'signed_at' => now(),
            'signed_by' => $validated['signed_by'],
        ]);

        $intervention->load(['typeIntervention', 'parties', 'rapports']);
        return new InterventionResource($intervention);
    }
}
