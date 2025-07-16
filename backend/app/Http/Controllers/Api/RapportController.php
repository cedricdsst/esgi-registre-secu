<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RapportResource;
use App\Models\Rapport;
use App\Models\Intervention;
use App\Models\TypeRapport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'observations' => 'nullable|array',
            'observations.*.identification' => 'required|string|max:255',
            'observations.*.libelle' => 'required|string|max:255',
            'observations.*.localisation' => 'required|string|max:255',
            'observations.*.priorite' => 'required|in:urgent,normal,faible',
            'observations.*.statut_traitement' => 'required|in:nouveau,en_cours,traite,reporte',
            'observations.*.deja_signalee' => 'boolean',
            'observations.*.date_signalement_precedent' => 'nullable|date',
            'observations.*.partie_ids' => 'nullable|array',
            'observations.*.partie_ids.*' => 'exists:parties,id',
        ]);

        $rapport = Rapport::create($validated);
        
        // Attacher les parties
        $rapport->parties()->attach($validated['partie_ids']);
        
        // Créer les observations si elles sont fournies
        if (isset($validated['observations']) && is_array($validated['observations'])) {
            foreach ($validated['observations'] as $observationData) {
                $partieIds = $observationData['partie_ids'] ?? [];
                unset($observationData['partie_ids']);
                
                $observation = $rapport->observations()->create($observationData);
                
                // Attacher les parties à l'observation
                if (!empty($partieIds)) {
                    $observation->parties()->attach($partieIds);
                }
            }
        }
        
        $rapport->load(['intervention', 'typeRapport', 'parties', 'observations', 'fichiers']);
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

    public function uploadFile(Request $request, Rapport $rapport)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB max
        ]);
        
        $file = $request->file('file');
        $nomStockage = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $cheminStockage = 'private/rapports/' . $nomStockage;
        
        // Créer le répertoire s'il n'existe pas
        if (!Storage::exists('private/rapports')) {
            Storage::makeDirectory('private/rapports');
        }
        
        // Stocker le fichier
        $path = $file->storeAs('private/rapports', $nomStockage);
        
        // Créer l'enregistrement en base
        $fichierRapport = $rapport->fichiers()->create([
            'nom_original' => $file->getClientOriginalName(),
            'nom_stockage' => $nomStockage,
            'version' => 1,
            'taille' => $file->getSize(),
            'type_mime' => $file->getMimeType(),
        ]);
        
        return response()->json([
            'message' => 'Fichier uploadé avec succès',
            'fichier' => [
                'id' => $fichierRapport->id,
                'nom_original' => $fichierRapport->nom_original,
                'taille' => $this->formatBytes($fichierRapport->taille),
                'version' => $fichierRapport->version,
                'type_mime' => $fichierRapport->type_mime,
                'created_at' => $fichierRapport->created_at,
            ]
        ], 201);
    }

    public function downloadFile(Rapport $rapport, $fichier_id)
    {
        $fichier = $rapport->fichiers()->findOrFail($fichier_id);
        
        $path = storage_path('app/private/rapports/' . $fichier->nom_stockage);
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'Fichier non trouvé'], 404);
        }
        
        return response()->download($path, $fichier->nom_original);
    }

    public function deleteFile(Rapport $rapport, $fichier_id)
    {
        $fichier = $rapport->fichiers()->findOrFail($fichier_id);
        
        // Supprimer le fichier du stockage
        $path = storage_path('app/private/rapports/' . $fichier->nom_stockage);
        if (file_exists($path)) {
            unlink($path);
        }
        
        // Supprimer l'enregistrement en base
        $fichier->delete();
        
        return response()->json(['message' => 'Fichier supprimé avec succès'], 200);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
