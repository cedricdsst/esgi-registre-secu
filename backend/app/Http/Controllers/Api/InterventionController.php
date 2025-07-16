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
    $user = $request->user();
    $query = Intervention::with(['typeIntervention', 'parties']);
    
    // Filtrer par créateur pour les utilisateurs entreprise seulement
    // Les super-admin peuvent voir toutes les interventions
    // Les user-intervenant voient seulement les interventions où ils sont assignés
    if ($user->hasRole('user-entreprise')) {
        $query->where('created_by', $user->id);
    } elseif ($user->hasRole('user-intervenant')) {
        $query->where('intervenant_nom', $user->prenom . ' ' . $user->nom);
    }
    
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

        // Ajouter l'utilisateur créateur
        $validated['created_by'] = $request->user()->id;

        $intervention = Intervention::create($validated);
        
        // Attacher les parties
        $intervention->parties()->attach($validated['partie_ids']);
        
        $intervention->load(['typeIntervention', 'parties']);
        return new InterventionResource($intervention);
    }

    /**
     * Récupérer les utilisateurs intervenants pour les formulaires
     */
    public function getIntervenantUsers(Request $request)
    {
        $user = $request->user();
        
        // Vérifier que l'utilisateur a le droit de créer des interventions
        if (!$user->hasRole(['super-admin', 'user-entreprise'])) {
            return response()->json([
                'message' => 'Accès interdit.'
            ], 403);
        }

        try {
            // Récupérer tous les utilisateurs avec le rôle 'user-intervenant'
            $intervenants = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'user-intervenant');
            })->select('id', 'nom', 'prenom', 'email', 'organisation')
              ->orderBy('nom')
              ->get();

            return response()->json($intervenants);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des intervenants.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Intervention $intervention)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur peut voir cette intervention
        // Les super-admin peuvent voir toutes les interventions
        // Les user-intervenant peuvent voir les interventions qui leur sont assignées
        if ($user->hasRole('user-entreprise') && $intervention->created_by !== $user->id) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        } elseif ($user->hasRole('user-intervenant') && $intervention->intervenant_nom !== ($user->prenom . ' ' . $user->nom)) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        }
        
        // Charger les observations de suivi seulement pour les interventions de type "Suivi d'observation"
        $relations = [
            'typeIntervention', 
            'parties.batiment', 
            'rapports.typeRapport',
            'rapports.observations',
            'rapports.fichiers'
        ];
        
        if ($intervention->typeIntervention && $intervention->typeIntervention->nom === 'Suivi d\'observation') {
            $relations[] = 'observationsSuivi';
        }
        
        $intervention->load($relations);
        return new InterventionResource($intervention);
    }

    public function update(Request $request, Intervention $intervention)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur peut modifier cette intervention
        if ($user->hasRole('user-entreprise') && $intervention->created_by !== $user->id) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        } elseif ($user->hasRole('user-intervenant') && $intervention->intervenant_nom !== ($user->prenom . ' ' . $user->nom)) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        }
        
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

    public function updateStatus(Request $request, Intervention $intervention)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur peut modifier cette intervention
        if ($user->hasRole('user-entreprise') && $intervention->created_by !== $user->id) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        } elseif ($user->hasRole('user-intervenant') && $intervention->intervenant_nom !== ($user->prenom . ' ' . $user->nom)) {
            return response()->json([
                'message' => 'Accès refusé à cette intervention.'
            ], 403);
        }
        
        $validated = $request->validate([
            'statut' => 'required|in:planifie,en_cours,termine,annule',
        ]);

        $intervention->update([
            'statut' => $validated['statut'],
        ]);

        $intervention->load(['typeIntervention', 'parties', 'rapports']);
        return new InterventionResource($intervention);
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
