<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LotResource;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('super-admin')) {
            $lots = Lot::with(['parties'])->get();
        } else {
            // Les utilisateurs ne voient que les lots des sites auxquels ils ont accès
            $lots = Lot::with(['parties'])
                ->whereHas('parties.niveau.batiment.site', function ($query) use ($user) {
                    $query->where('client_id', $user->id)
                          ->orWhereHas('droitsSite', function ($q) use ($user) {
                              $q->where('utilisateur_id', $user->id)
                                ->where('lecture', true);
                          });
                })
                ->get();
        }

        return response()->json([
            'lots' => LotResource::collection($lots)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'niveau' => 'required|integer',
            'type' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Seuls les admins peuvent créer des lots
        if (!$user->hasRole(['admin', 'super-admin'])) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer un lot.'
            ], 403);
        }

        $lot = Lot::create([
            'nom' => $request->nom,
            'niveau' => $request->niveau,
            'type' => $request->type,
        ]);

        return response()->json([
            'message' => 'Lot créé avec succès',
            'lot' => new LotResource($lot)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lot $lot)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès via les parties liées
        if (!$user->hasRole('super-admin')) {
            $hasAccess = $lot->parties()
                ->whereHas('niveau.batiment.site', function ($query) use ($user) {
                    $query->where('client_id', $user->id)
                          ->orWhereHas('droitsSite', function ($q) use ($user) {
                              $q->where('utilisateur_id', $user->id)
                                ->where('lecture', true);
                          });
                })
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Vous n\'avez pas les droits pour consulter ce lot.'
                ], 403);
            }
        }

        return response()->json([
            'lot' => new LotResource($lot->load(['parties.niveau.batiment.site']))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lot $lot)
    {
        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'niveau' => 'sometimes|integer',
            'type' => 'sometimes|string|max:255',
        ]);

        $user = Auth::user();

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin')) {
            $hasWriteAccess = $lot->parties()
                ->whereHas('niveau.batiment.site', function ($query) use ($user) {
                    $query->where('client_id', $user->id)
                          ->orWhereHas('droitsSite', function ($q) use ($user) {
                              $q->where('utilisateur_id', $user->id)
                                ->where('ecriture', true);
                          });
                })
                ->exists();

            if (!$hasWriteAccess) {
                return response()->json([
                    'message' => 'Vous n\'avez pas les droits pour modifier ce lot.'
                ], 403);
            }
        }

        $lot->update($request->only(['nom', 'niveau', 'type']));

        return response()->json([
            'message' => 'Lot mis à jour avec succès',
            'lot' => new LotResource($lot)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lot $lot)
    {
        $user = Auth::user();

        // Seuls les super-admins peuvent supprimer des lots
        if (!$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour supprimer ce lot.'
            ], 403);
        }

        $lot->delete();

        return response()->json([
            'message' => 'Lot supprimé avec succès'
        ]);
    }
}
