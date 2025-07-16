<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatimentResource;
use App\Models\Batiment;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('super-admin')) {
            $batiments = Batiment::with(['site', 'niveaux', 'parties.owner', 'erps', 'ighs', 'habs', 'bups'])->get();
        } elseif ($user->hasRole('user-entreprise')) {
            // Pour les utilisateurs entreprise, ne montrer que les bâtiments avec des parties qui leur appartiennent
            $batiments = Batiment::with(['site', 'niveaux', 'parties.owner', 'erps', 'ighs', 'habs', 'bups'])
                ->whereHas('parties', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                })
                ->get();
        } else {
            $batiments = Batiment::with(['site', 'niveaux', 'parties.owner', 'erps', 'ighs', 'habs', 'bups'])
                ->whereHas('site', function ($query) use ($user) {
                    $query->where('client_id', $user->id)
                          ->orWhereHas('droitsSite', function ($q) use ($user) {
                              $q->where('utilisateur_id', $user->id)
                                ->where('lecture', true);
                          });
                })
                ->orWhereHas('droitsBatiment', function ($query) use ($user) {
                    $query->where('utilisateur_id', $user->id)
                          ->where('lecture', true);
                })
                ->get();
        }

        return response()->json(BatimentResource::collection($batiments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:ERP,IGH,HAB,BUP,ICPE',
            'isICPE' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $site = Site::findOrFail($request->site_id);

        // Vérifier les droits d'écriture sur le site
        if (!$user->hasRole('super-admin') && 
            $site->client_id !== $user->id && 
            !$site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer un bâtiment sur ce site.'
            ], 403);
        }

        $batiment = Batiment::create([
            'site_id' => $request->site_id,
            'name' => $request->name,
            'type' => $request->type,
            'isICPE' => $request->isICPE ?? false,
        ]);

        return response()->json([
            'message' => 'Bâtiment créé avec succès',
            'batiment' => new BatimentResource($batiment->load(['site', 'niveaux']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Batiment $batiment)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès
        $canAccess = false;
        
        if ($user->hasRole('super-admin')) {
            $canAccess = true;
        } elseif ($batiment->site->client_id === $user->id) {
            $canAccess = true;
        } elseif ($batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            $canAccess = true;
        } elseif ($batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            $canAccess = true;
        } elseif ($user->hasRole('user-entreprise')) {
            // Vérifier si l'utilisateur est propriétaire d'au moins une partie dans ce bâtiment
            $canAccess = $batiment->parties()->where('owner_id', $user->id)->exists();
        }

        if (!$canAccess) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour consulter ce bâtiment.'
            ], 403);
        }

        return response()->json([
            'batiment' => new BatimentResource($batiment->load([
                'site', 
                'niveaux.parties.owner', 
                'parties.owner',
                'parties.lots', 
                'parties.niveaux',
                'erps', 
                'ighs', 
                'habs', 
                'bups',
                'droitsBatiment'
            ]))
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Batiment $batiment)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:ERP,IGH,HAB,BUP,ICPE',
            'isICPE' => 'sometimes|boolean',
        ]);

        $user = Auth::user();

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $batiment->site->client_id !== $user->id && 
            !$batiment->site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists() &&
            !$batiment->droitsBatiment()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier ce bâtiment.'
            ], 403);
        }

        $batiment->update($request->only(['name', 'type', 'isICPE']));

        return response()->json([
            'message' => 'Bâtiment mis à jour avec succès',
            'batiment' => new BatimentResource($batiment->load(['site', 'niveaux']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Batiment $batiment)
    {
        $user = Auth::user();

        // Seuls les super-admins peuvent supprimer des bâtiments
        if (!$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour supprimer ce bâtiment.'
            ], 403);
        }

        $batiment->delete();

        return response()->json([
            'message' => 'Bâtiment supprimé avec succès'
        ]);
    }
}
