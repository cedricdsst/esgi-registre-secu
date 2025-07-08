<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Si l'utilisateur est super-admin, il voit tous les sites
        if ($user->hasRole('super-admin')) {
            $sites = Site::with(['batiments', 'client'])->get();
        } else {
            // Sinon, il ne voit que les sites de son organisation ou ceux auxquels il a des droits
            $sites = Site::with(['batiments', 'client'])
                ->where(function ($query) use ($user) {
                    $query->where('client_id', $user->id)
                          ->orWhereHas('droitsSite', function ($q) use ($user) {
                              $q->where('utilisateur_id', $user->id)
                                ->where('lecture', true);
                          });
                })
                ->get();
        }

        return response()->json(SiteResource::collection($sites)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'pays' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'client_id' => 'sometimes|exists:users,id',
        ]);

        $user = Auth::user();

        // Seuls les admins peuvent créer des sites
        if (!$user->hasRole(['admin', 'super-admin'])) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour créer un site.'
            ], 403);
        }

        $site = Site::create([
            'nom' => $request->nom,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'ville' => $request->ville,
            'pays' => $request->pays ?? 'France',
            'description' => $request->description,
            'client_id' => $request->client_id,
        ]);

        return response()->json([
            'message' => 'Site créé avec succès',
            'site' => new SiteResource($site->load(['batiments', 'client']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        $user = Auth::user();

        // Vérifier les droits d'accès
        if (!$user->hasRole('super-admin') && 
            $site->client_id !== $user->id && 
            !$site->droitsSite()->where('utilisateur_id', $user->id)->where('lecture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour consulter ce site.'
            ], 403);
        }

        return response()->json([
            'site' => new SiteResource($site->load(['batiments.niveaux.parties', 'client', 'droitsSite.user']))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'sometimes|string|max:255',
            'code_postal' => 'sometimes|string|max:10',
            'ville' => 'sometimes|string|max:255',
            'pays' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'client_id' => 'sometimes|exists:users,id',
        ]);

        $user = Auth::user();

        // Vérifier les droits d'écriture
        if (!$user->hasRole('super-admin') && 
            $site->client_id !== $user->id && 
            !$site->droitsSite()->where('utilisateur_id', $user->id)->where('ecriture', true)->exists()) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour modifier ce site.'
            ], 403);
        }

        $site->update($request->only([
            'nom', 'adresse', 'code_postal', 'ville', 'pays', 'description', 'client_id'
        ]));

        return response()->json([
            'message' => 'Site mis à jour avec succès',
            'site' => new SiteResource($site->load(['batiments', 'client']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $user = Auth::user();

        // Seuls les super-admins peuvent supprimer des sites
        if (!$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Vous n\'avez pas les droits pour supprimer ce site.'
            ], 403);
        }

        $site->delete();

        return response()->json([
            'message' => 'Site supprimé avec succès'
        ]);
    }
}
