<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartieResource;
use App\Models\Partie;
use App\Models\Niveau;
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
        
        // Si un niveau_id est fourni, filtrer par niveau
        $query = Partie::with(['niveau.batiment.site', 'lots']);
        
        if ($request->has('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }
        
        if ($user->hasRole('super-admin')) {
            $parties = $query->get();
        } else {
            $parties = $query->whereHas('niveau.batiment.site', function ($q) use ($user) {
                $q->where('client_id', $user->id)
                  ->orWhereHas('droitsSite', function ($sq) use ($user) {
                      $sq->where('utilisateur_id', $user->id)
                         ->where('lecture', true);
                  });
            })
            ->orWhereHas('niveau.batiment.droitsBatiment', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->orWhereHas('niveau.droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->orWhereHas('droitsPartie', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->get();
        }

        return response()->json([
            'parties' => PartieResource::collection($parties)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:privative,commune',
            'isICPE' => 'sometimes|boolean',
            'isPrivative' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $niveau = Niveau::with('batiment.site')->findOrFail($request->niveau_id);

        // Vérifier les droits d'écriture sur le niveau, bâtiment ou site
        if (!$user->hasRole('super-admin') && 
            $niveau->batiment->site->client_id !== $user->id && 
            !$niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer une partie sur ce niveau.'
            ], 403);
        }

        $partie = Partie::create([
            'niveau_id' => $request->niveau_id,
            'nom' => $request->nom,
            'type' => $request->type,
            'isICPE' => $request->isICPE ?? false,
            'isPrivative' => $request->isPrivative ?? ($request->type === 'privative'),
        ]);

        return response()->json([
            'message' => 'Partie créée avec succès',
            'partie' => new PartieResource($partie->load(['niveau.batiment.site', 'lots']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partie $partie)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès
        if (!$user->hasRole('super-admin') && 
            $partie->niveau->batiment->site->client_id !== $user->id && 
            !$partie->niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$partie->niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$partie->niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour consulter cette partie.'
            ], 403);
        }

        return response()->json([
            'partie' => new PartieResource($partie->load([
                'niveau.batiment.site', 
                'lots' => function ($query) {
                    $query->withPivot('libelle', 'type');
                },
                'droitsPartie.user'
            ]))
        ]);
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
            $partie->niveau->batiment->site->client_id !== $user->id && 
            !$partie->niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier cette partie.'
            ], 403);
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
            'partie' => new PartieResource($partie->load(['niveau.batiment.site', 'lots']))
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

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $partie->niveau->batiment->site->client_id !== $user->id && 
            !$partie->niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->droitsPartie()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier cette partie.'
            ], 403);
        }

        $pivotData = [];
        if ($request->has('libelle')) {
            $pivotData['libelle'] = $request->libelle;
        }
        if ($request->has('type')) {
            $pivotData['type'] = $request->type;
        }

        $partie->lots()->attach($request->lot_id, $pivotData);

        return response()->json([
            'message' => 'Lot attaché avec succès à la partie',
            'partie' => new PartieResource($partie->load(['lots' => function ($query) {
                $query->withPivot('libelle', 'type');
            }]))
        ]);
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

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $partie->niveau->batiment->site->client_id !== $user->id && 
            !$partie->niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$partie->niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
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
}
