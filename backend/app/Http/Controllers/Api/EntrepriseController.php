<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EntrepriseResource;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::orderBy('nom')->get();
        return $entreprises;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_organisme_agree' => 'boolean',
        ]);

        $entreprise = Entreprise::create($validated);
        return new EntrepriseResource($entreprise);
    }

    public function show(Entreprise $entreprise)
    {
        return new EntrepriseResource($entreprise);
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $validated = $request->validate([
            'nom' => 'string|max:255',
            'contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_organisme_agree' => 'boolean',
        ]);

        $entreprise->update($validated);
        return new EntrepriseResource($entreprise);
    }

    public function destroy(Entreprise $entreprise)
    {
        $entreprise->delete();
        return response()->json(['message' => 'Entreprise supprimée avec succès'], Response::HTTP_OK);
    }
}
