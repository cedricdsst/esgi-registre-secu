<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartieResource;
use App\Models\Partie;
use App\Models\Niveau;
use App\Models\Batiment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Si un batiment_id est fourni, filtrer par bâtiment
        $query = Partie::with(['batiment.site', 'niveaux', 'lots']);
        
        if ($request->has('batiment_id')) {
            $query->where('batiment_id', $request->batiment_id);
        }
        
        // Si un niveau_id est fourni, filtrer par niveau via la relation pivot
        if ($request->has('niveau_id')) {
            $query->whereHas('niveaux', function ($q) use ($request) {
                $q->where('niveaux.id', $request->niveau_id);
            });
        }
        
        if ($user->hasRole('super-admin')) {
            $parties = $query->get();
        } else {
            $parties = $query->whereHas('batiment.site', function ($q) use ($user) {
                $q->where('client_id', $user->id)
                  ->orWhereHas('droitsSite', function ($sq) use ($user) {
                      $sq->where('utilisateur_id', $user->id)
                         ->where('lecture', true);
                  });
            })
            ->orWhereHas('batiment.droitsBatiment', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->orWhereHas('niveaux.droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->orWhereHas('droitsPartie', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->get();
        }

        return response()->json(
            PartieResource::collection($parties)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'batiment_id' => 'required|exists:batiments,id',
            'niveau_ids' => 'required|array',
            'niveau_ids.*' => 'required|exists:niveaux,id',
            'niveaux_data' => 'sometimes|array',
            'niveaux_data.*.niveau_id' => 'required|exists:niveaux,id',
            'niveaux_data.*.libelle' => 'sometimes|string|max:255',
            'niveaux_data.*.effectif_public' => 'sometimes|integer|min:0',
            'niveaux_data.*.personnel' => 'sometimes|integer|min:0',
            'niveaux_data.*.surface_exploitation' => 'sometimes|numeric|min:0',
            'niveaux_data.*.surface_gla' => 'sometimes|numeric|min:0',
            'niveaux_data.*.surface_accessible_public' => 'sometimes|numeric|min:0',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:privative,commune',
            'isICPE' => 'sometimes|boolean',
            'isPrivative' => 'sometimes|boolean',
            'activites_erp' => 'sometimes|array',
        ]);

        $user = Auth::user();
        $batiment = Batiment::with('site')->findOrFail($request->batiment_id);

        // Vérifier les droits d'écriture sur le bâtiment ou site
        if (!$user->hasRole('super-admin') && 
            $batiment->site->client_id !== $user->id && 
            !$batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer une partie sur ce bâtiment.'
            ], 403);
        }

        // Vérifier les règles métier selon la typologie du bâtiment
        if ($batiment->type === 'HAB' && $request->type === 'privative') {
            return response()->json([
                'message' => 'Les parties privatives ne sont pas autorisées pour les bâtiments de type HAB.',
                'error' => 'business_rule_violation'
            ], 422);
        }

        $partie = Partie::create([
            'batiment_id' => $request->batiment_id,
            'nom' => $request->nom,
            'type' => $request->type,
            'isICPE' => $request->isICPE ?? false,
            'isPrivative' => $request->isPrivative ?? ($request->type === 'privative'),
            'activites_erp' => $request->activites_erp ? json_encode($request->activites_erp) : null,
        ]);

        // Associer les niveaux avec leurs données spécifiques
        $niveauxData = $request->niveaux_data ?? [];
        
        foreach ($request->niveau_ids as $niveauId) {
            // Chercher les données spécifiques pour ce niveau
            $niveauData = collect($niveauxData)->firstWhere('niveau_id', $niveauId);
            
            $pivotData = [
                'libelle' => $niveauData['libelle'] ?? null,
                'effectif_public' => $niveauData['effectif_public'] ?? null,
                'personnel' => $niveauData['personnel'] ?? null,
                'surface_exploitation' => $niveauData['surface_exploitation'] ?? null,
                'surface_gla' => $niveauData['surface_gla'] ?? null,
                'surface_accessible_public' => $niveauData['surface_accessible_public'] ?? null,
            ];
            
            $partie->niveaux()->attach($niveauId, $pivotData);
        }

        // Mettre à jour le statut ICPE du bâtiment si nécessaire
        $partie->updateBatimentICPE();

        return response()->json(
            new PartieResource($partie->load(['batiment.site', 'niveaux', 'lots']))
        , 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partie $partie)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès
        if (!$user->hasRole('super-admin') && 
            $partie->batiment->site->client_id !== $user->id && 
            !$partie->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$partie->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$partie->niveaux()->whereHas('droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)->where('lecture', true);
            })->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour consulter cette partie.'
            ], 403);
        }

        return response()->json(
            new PartieResource($partie->load([
                'batiment.site', 
                'niveaux',
                'lots' => function ($query) {
                    $query->withPivot('libelle', 'type');
                },
                'droitsPartie.user'
            ]))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partie $partie)
    {
        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:privative,commune',
            'isICPE' => 'sometimes|boolean',
            'isPrivative' => 'sometimes|boolean',
        ]);

        $user = Auth::user();

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $partie->batiment->site->client_id !== $user->id && 
            !$partie->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveaux()->whereHas('droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)->where('ecriture', true);
            })->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier cette partie.'
            ], 403);
        }

        // Vérifier les règles métier HAB lors de la modification
        if ($request->has('type') && $request->type === 'privative' && $partie->batiment->type === 'HAB') {
            return response()->json([
                'message' => 'Les parties privatives ne sont pas autorisées pour les bâtiments de type HAB.',
                'error' => 'business_rule_violation'
            ], 422);
        }

        // Si le type change vers 'privative', mettre à jour isPrivative
        $updateData = $request->only(['nom', 'type', 'isICPE', 'isPrivative']);
        if ($request->has('type') && $request->type === 'privative') {
            $updateData['isPrivative'] = true;
        } elseif ($request->has('type') && $request->type === 'commune') {
            $updateData['isPrivative'] = false;
        }

        $partie->update($updateData);

        return response()->json([
            'message' => 'Partie mise à jour avec succès',
            'partie' => new PartieResource($partie->load(['batiment.site', 'niveaux', 'lots']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partie $partie)
    {
        $user = Auth::user();

        // Seuls les super-admins peuvent supprimer des parties
        if (!$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour supprimer cette partie.'
            ], 403);
        }

        $partie->delete();

        return response()->json([
            'message' => 'Partie supprimée avec succès'
        ]);
    }

    /**
     * Attach a lot to a partie
     */
    public function attachLot(Request $request, Partie $partie)
    {
        $request->validate([
            'lot_id' => 'required|exists:lots,id',
            'libelle' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
        ]);

        $user = Auth::user();

        // Vérifier si les lots sont autorisés pour ce type de bâtiment
        if ($partie->batiment->type === 'HAB') {
            return response()->json([
                'message' => 'Les lots ne sont pas disponibles pour les bâtiments de type HAB.',
                'error' => 'lots_not_available_for_hab'
            ], 422);
        }

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $partie->batiment->site->client_id !== $user->id && 
            !$partie->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier cette partie.'
            ], 403);
        }

        // Vérifier les conflits de lots selon les consignes métier (avertissement seulement)
        $conflicts = $partie->checkLotConflicts($request->lot_id);
        $warnings = [];
        
        if (!empty($conflicts)) {
            $warnings[] = 'Ce lot est déjà occupé par d\'autres parties : ' . implode(', ', $conflicts);
        }

        $pivotData = [];
        if ($request->has('libelle')) {
            $pivotData['libelle'] = $request->libelle;
        }
        if ($request->has('type')) {
            $pivotData['type'] = $request->type;
        }

        $partie->lots()->attach($request->lot_id, $pivotData);

        $response = [
            'message' => 'Lot attaché avec succès à la partie',
            'partie' => new PartieResource($partie->load(['lots' => function ($query) {
                $query->withPivot('libelle', 'type');
            }]))
        ];

        if (!empty($warnings)) {
            $response['warnings'] = $warnings;
        }

        return response()->json($response);
    }

    /**
     * Detach a lot from a partie
     */
    public function detachLot(Request $request, Partie $partie)
    {
        $request->validate([
            'lot_id' => 'required|exists:lots,id',
        ]);

        $user = Auth::user();

        // Vérifier si les lots sont autorisés pour ce type de bâtiment
        if ($partie->batiment->type === 'HAB') {
            return response()->json([
                'message' => 'Les lots ne sont pas disponibles pour les bâtiments de type HAB.',
                'error' => 'lots_not_available_for_hab'
            ], 422);
        }

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $partie->batiment->site->client_id !== $user->id && 
            !$partie->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveaux()->whereHas('droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)->where('ecriture', true);
            })->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier cette partie.'
            ], 403);
        }

        $partie->lots()->detach($request->lot_id);

        return response()->json([
            'message' => 'Lot détaché avec succès de la partie'
        ]);
    }

    /**
     * Assign an owner to one or multiple parties
     */
    public function assignOwner(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'partie_ids' => 'required|array|min:1',
            'partie_ids.*' => 'required|exists:parties,id',
        ]);

        $user = Auth::user();
        $partieIds = $request->partie_ids;

        // Récupérer toutes les parties pour vérifier les droits
        $parties = Partie::with('batiment.site')->whereIn('id', $partieIds)->get();

        // Vérifier que toutes les parties existent
        if ($parties->count() !== count($partieIds)) {
            return response()->json([
                'message' => 'Une ou plusieurs parties n\'existent pas.'
            ], 404);
        }

        // Vérifier les droits d'écriture pour chaque partie
        foreach ($parties as $partie) {
            if (!$user->hasRole('super-admin') && 
                $partie->batiment->site->client_id !== $user->id && 
                !$partie->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
                !$partie->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
                !$partie->niveaux()->whereHas('droitsNiveau', function ($q) use ($user) {
                    $q->where('utilisateur_id', $user->id)->where('ecriture', true);
                })->exists() &&
                !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
                return response()->json([
                    'message' => "Vous n'avez pas les droits pour modifier la partie \"{$partie->nom}\"."
                ], 403);
            }
        }

        // Assigner l'owner à toutes les parties
        Partie::whereIn('id', $partieIds)->update(['owner_id' => $request->owner_id]);

        // Recharger les parties avec les nouvelles données
        $updatedParties = Partie::with(['batiment.site', 'owner'])->whereIn('id', $partieIds)->get();

        return response()->json([
            'message' => 'Propriétaire assigné avec succès aux parties',
            'parties' => PartieResource::collection($updatedParties)
        ]);
    }
}
