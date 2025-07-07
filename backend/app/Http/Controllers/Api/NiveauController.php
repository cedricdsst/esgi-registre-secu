<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NiveauResource;
use App\Models\Niveau;
use App\Models\Batiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NiveauController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Si un batiment_id est fourni, filtrer par bâtiment
        $query = Niveau::with(['batiment.site', 'parties']);
        
        if ($request->has('batiment_id')) {
            $query->where('batiment_id', $request->batiment_id);
        }
        
        if ($user->hasRole('super-admin')) {
            $niveaux = $query->get();
        } else {
            $niveaux = $query->whereHas('batiment.site', function ($q) use ($user) {
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
            ->orWhereHas('droitsNiveau', function ($q) use ($user) {
                $q->where('utilisateur_id', $user->id)
                  ->where('lecture', true);
            })
            ->get();
        }

        return response()->json([
            'niveaux' => NiveauResource::collection($niveaux)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'batiment_id' => 'required|exists:batiments,id',
            'nom' => 'required|string|max:255',
            'numero_etage' => 'required|integer',
            'description' => 'sometimes|string',
        ]);

        $user = Auth::user();
        $batiment = Batiment::findOrFail($request->batiment_id);

        // Vérifier les droits d'écriture sur le bâtiment ou le site
        if (!$user->hasRole('super-admin') && 
            $batiment->site->client_id !== $user->id && 
            !$batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer un niveau sur ce bâtiment.'
            ], 403);
        }

        $niveau = Niveau::create([
            'batiment_id' => $request->batiment_id,
            'nom' => $request->nom,
            'numero_etage' => $request->numero_etage,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Niveau créé avec succès',
            'niveau' => new NiveauResource($niveau->load(['batiment.site', 'parties']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Niveau $niveau)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès
        if (!$user->hasRole('super-admin') && 
            $niveau->batiment->site->client_id !== $user->id && 
            !$niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('lecture', true)->exists() &&
            !$niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour consulter ce niveau.'
            ], 403);
        }

        return response()->json([
            'niveau' => new NiveauResource($niveau->load([
                'batiment.site', 
                'parties.lots', 
                'droitsNiveau.user'
            ]))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Niveau $niveau)
    {
        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'numero_etage' => 'sometimes|integer',
            'description' => 'sometimes|string',
        ]);

        $user = Auth::user();

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $niveau->batiment->site->client_id !== $user->id && 
            !$niveau->batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$niveau->batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$niveau->droitsNiveau()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier ce niveau.'
            ], 403);
        }

        $niveau->update($request->only(['nom', 'numero_etage', 'description']));

        return response()->json([
            'message' => 'Niveau mis à jour avec succès',
            'niveau' => new NiveauResource($niveau->load(['batiment.site', 'parties']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Niveau $niveau)
    {
        $user = Auth::user();

        // Seuls les super-admins peuvent supprimer des niveaux
        if (!$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour supprimer ce niveau.'
            ], 403);
        }

        $niveau->delete();

        return response()->json([
            'message' => 'Niveau supprimé avec succès'
        ]);
    }
}
