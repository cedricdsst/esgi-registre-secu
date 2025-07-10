<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ObservationResource;
use App\Http\Resources\InterventionResource;
use App\Models\Observation;
use App\Models\Rapport;
use App\Models\TypeIntervention;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Log;

class ObservationController extends Controller
{
    // Constantes pour la compression
    private const MAX_FILE_SIZE_KB = 300; // 300KB objectif
    private const MAX_IMAGE_WIDTH = 1280;
    private const MAX_IMAGE_HEIGHT = 720;
    private const JPEG_QUALITY_START = 85;
    private const JPEG_QUALITY_MIN = 40;
    private const WEBP_QUALITY_START = 80;
    private const WEBP_QUALITY_MIN = 40;

    public function index(Request $request)
    {
        $query = Observation::with(['rapport', 'parties']);
        
        // Filtres
        if ($request->has('rapport_id')) {
            $query->where('rapport_id', $request->rapport_id);
        }
        
        if ($request->has('statut_traitement')) {
            $query->where('statut_traitement', $request->statut_traitement);
        }
        
        if ($request->has('priorite')) {
            $query->where('priorite', $request->priorite);
        }
        
        if ($request->has('partie_id')) {
            $query->whereHas('parties', function ($q) use ($request) {
                $q->where('parties.id', $request->partie_id);
            });
        }

        $observations = $query->orderBy('created_at', 'desc')->get();
        return $observations;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rapport_id' => 'required|exists:rapports,id',
            'identification' => 'required|string|max:255',
            'libelle' => 'required|string',
            'localisation' => 'nullable|string|max:255',
            'priorite' => 'nullable|in:urgent,normal,faible',
            'deja_signalee' => 'boolean',
            'date_signalement_precedent' => 'nullable|date',
            'partie_ids' => 'required|array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $observation = Observation::create($validated);
        
        // Attacher les parties
        $observation->parties()->attach($validated['partie_ids']);
        
        $observation->load(['rapport', 'parties']);
        return new ObservationResource($observation);
    }

    public function show(Observation $observation)
    {
        $observation->load(['rapport', 'parties', 'fichiers']);
        return new ObservationResource($observation);
    }

    public function update(Request $request, Observation $observation)
    {
        $validated = $request->validate([
            'identification' => 'string|max:255',
            'libelle' => 'string',
            'localisation' => 'nullable|string|max:255',
            'priorite' => 'nullable|in:urgent,normal,faible',
            'statut_traitement' => 'in:nouveau,en_cours,traite,reporte',
            'deja_signalee' => 'boolean',
            'date_signalement_precedent' => 'nullable|date',
            'partie_ids' => 'array|min:1',
            'partie_ids.*' => 'exists:parties,id',
        ]);

        $observation->update($validated);
        
        // Mettre à jour les parties si fourni
        if (isset($validated['partie_ids'])) {
            $observation->parties()->sync($validated['partie_ids']);
        }
        
        $observation->load(['rapport', 'parties', 'fichiers']);
        return new ObservationResource($observation);
    }

    public function destroy(Observation $observation)
    {
        $observation->delete();
        return response()->json(['message' => 'Observation supprimée avec succès'], Response::HTTP_OK);
    }

    public function createFollowUpIntervention(Request $request)
    {
        $validated = $request->validate([
            'observation_ids' => 'required|array|min:1',
            'observation_ids.*' => 'exists:observations,id',
            'intitule' => 'required|string|max:255',
            'entreprise_nom' => 'required|string|max:255',
            'intervenant_nom' => 'required|string|max:255',
        ]);

        // Récupérer le type d'intervention "Suivi d'observation"
        $typeIntervention = TypeIntervention::where('nom', 'Suivi d\'observation')->first();
        
        if (!$typeIntervention) {
            return response()->json([
                'message' => 'Type d\'intervention "Suivi d\'observation" non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Récupérer toutes les observations
        $observations = Observation::with('parties')->whereIn('id', $validated['observation_ids'])->get();
        
        // Collecter toutes les parties concernées
        $partieIds = $observations->pluck('parties')->flatten()->pluck('id')->unique();

        // Créer l'intervention
        $intervention = Intervention::create([
            'intitule' => $validated['intitule'],
            'entreprise_nom' => $validated['entreprise_nom'],
            'intervenant_nom' => $validated['intervenant_nom'],
            'type_intervention_id' => $typeIntervention->id,
            'statut' => 'planifie',
        ]);

        // Attacher les parties
        $intervention->parties()->attach($partieIds);

        // ✅ NOUVEAU : Lier les observations à cette intervention de suivi
        $intervention->observationsSuivi()->attach($validated['observation_ids']);

        // Mettre à jour le statut des observations
        $observations->each(function ($observation) {
            $observation->update(['statut_traitement' => 'en_cours']);
        });

        $intervention->load(['typeIntervention', 'parties', 'observationsSuivi']);
        return new InterventionResource($intervention);
    }

    public function uploadFile(Request $request, Observation $observation)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max en entrée
            'version' => 'nullable|integer|min:1',
        ]);

        $file = $request->file('file');
        $version = $request->input('version', 1);
        $tailleOriginale = $file->getSize();
        
        // Générer un nom unique pour le stockage
        $nomStockage = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Stocker le fichier (Laravel stocke dans storage/app/private/observations/)
        $cheminStockage = $file->storeAs('observations', $nomStockage);
        
        // Le fichier se trouve réellement dans storage/app/private/observations/
        $cheminComplet = storage_path('app/private/observations/' . $nomStockage);
        
        // Compression selon le type de fichier
        $tailleFinale = $tailleOriginale;
        $compressionReussie = false;
        $detailsCompression = [];
        
        $mimeType = $file->getClientMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            [$compressionReussie, $detailsCompression] = $this->compressImage($cheminComplet, $mimeType);
            if ($compressionReussie) {
                $tailleFinale = filesize($cheminComplet);
            }
        } elseif ($mimeType === 'application/pdf') {
            [$compressionReussie, $detailsCompression] = $this->compressPDF($cheminComplet);
            if ($compressionReussie) {
                $tailleFinale = filesize($cheminComplet);
            }
        }
        
        // Créer l'enregistrement en base avec la taille finale
        $fichierRapport = $observation->fichiers()->create([
            'nom_original' => $file->getClientOriginalName(),
            'nom_stockage' => $nomStockage,
            'version' => $version,
            'taille' => $tailleFinale,
            'type_mime' => $mimeType,
        ]);
        
        $gainEspace = $tailleOriginale - $tailleFinale;
        $pourcentageCompression = $tailleOriginale > 0 ? round(($gainEspace / $tailleOriginale) * 100, 1) : 0;
        
        return response()->json([
            'message' => 'Fichier uploadé avec succès',
            'fichier' => [
                'id' => $fichierRapport->id,
                'nom_original' => $fichierRapport->nom_original,
                'taille' => $fichierRapport->getSize(),
                'version' => $fichierRapport->version,
                'type_mime' => $fichierRapport->type_mime,
                'created_at' => $fichierRapport->created_at,
            ],
            'compression' => [
                'reussie' => $compressionReussie,
                'taille_originale' => $this->formatBytes($tailleOriginale),
                'taille_finale' => $this->formatBytes($tailleFinale),
                'gain_espace' => $this->formatBytes($gainEspace),
                'pourcentage_reduction' => $pourcentageCompression . '%',
                'details' => $detailsCompression
            ]
        ], 201);
    }

    public function downloadFile(Observation $observation, $fichier_id)
    {
        $fichier = $observation->fichiers()->findOrFail($fichier_id);
        
        // Utiliser le chemin correct découvert
        $path = storage_path('app/private/observations/' . $fichier->nom_stockage);
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'Fichier non trouvé'], 404);
        }
        
        return response()->download($path, $fichier->nom_original);
    }

    public function deleteFile(Observation $observation, $fichier_id)
    {
        $fichier = $observation->fichiers()->findOrFail($fichier_id);
        
        // Utiliser le chemin correct découvert
        $path = storage_path('app/private/observations/' . $fichier->nom_stockage);
        if (file_exists($path)) {
            unlink($path);
        }
        
        // Supprimer l'enregistrement en base
        $fichier->delete();
        
        return response()->json(['message' => 'Fichier supprimé avec succès']);
    }

    /**
     * Compression d'images avec algorithme progressif
     */
    private function compressImage($cheminFichier, $typeMime)
    {
        try {
            $details = ['etapes' => []];
            
            // Vérifier si GD est disponible
            if (!extension_loaded('gd')) {
                throw new Exception('Extension GD non disponible');
            }
            
            // Diagnostics du fichier
            $details['etapes'][] = "Type MIME détecté: " . $typeMime;
            $details['etapes'][] = "Taille fichier: " . $this->formatBytes(filesize($cheminFichier));
            
            // Vérifier le type réel du fichier avec getimagesize
            $imageInfo = @getimagesize($cheminFichier);
            if (!$imageInfo) {
                throw new Exception('Fichier image corrompu ou format non supporté par GD');
            }
            
            $details['etapes'][] = "Type réel détecté: " . image_type_to_mime_type($imageInfo[2]);
            $details['etapes'][] = "Dimensions détectées: {$imageInfo[0]}x{$imageInfo[1]}";
            
            // Utiliser le type réel plutôt que le type MIME déclaré
            $typeReel = image_type_to_mime_type($imageInfo[2]);
            
            // Lire l'image selon son type réel
            $image = null;
            switch ($typeReel) {
                case 'image/jpeg':
                    $image = @imagecreatefromjpeg($cheminFichier);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($cheminFichier);
                    break;
                case 'image/gif':
                    $image = @imagecreatefromgif($cheminFichier);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($cheminFichier);
                    } else {
                        throw new Exception('Support WebP non disponible dans cette installation PHP');
                    }
                    break;
                default:
                    throw new Exception('Type d\'image non supporté: ' . $typeReel);
            }
            
            if (!$image) {
                throw new Exception('Impossible de lire l\'image - fichier possiblement corrompu');
            }
            
            $details['etapes'][] = "Image chargée avec succès";
            
            $tailleOriginale = filesize($cheminFichier);
            $details['etapes'][] = "Image originale: " . $this->formatBytes($tailleOriginale);
            
            // Obtenir les dimensions
            $largeurOriginale = imagesx($image);
            $hauteurOriginale = imagesy($image);
            $details['etapes'][] = "Dimensions vérifiées: {$largeurOriginale}x{$hauteurOriginale}";
            
            // Étape 1: Redimensionner si nécessaire
            if ($largeurOriginale > self::MAX_IMAGE_WIDTH || $hauteurOriginale > self::MAX_IMAGE_HEIGHT) {
                $ratio = min(self::MAX_IMAGE_WIDTH / $largeurOriginale, self::MAX_IMAGE_HEIGHT / $hauteurOriginale);
                $nouvelleLargeur = round($largeurOriginale * $ratio);
                $nouvelleHauteur = round($hauteurOriginale * $ratio);
                
                $imageRedimensionnee = imagecreatetruecolor($nouvelleLargeur, $nouvelleHauteur);
                
                // Préserver la transparence pour PNG
                if ($typeReel === 'image/png') {
                    imagealphablending($imageRedimensionnee, false);
                    imagesavealpha($imageRedimensionnee, true);
                    $transparent = imagecolorallocatealpha($imageRedimensionnee, 0, 0, 0, 127);
                    imagefill($imageRedimensionnee, 0, 0, $transparent);
                }
                
                imagecopyresampled($imageRedimensionnee, $image, 0, 0, 0, 0, $nouvelleLargeur, $nouvelleHauteur, $largeurOriginale, $hauteurOriginale);
                imagedestroy($image);
                $image = $imageRedimensionnee;
                
                $details['etapes'][] = "Redimensionnement: {$nouvelleLargeur}x{$nouvelleHauteur}";
            }
            
            // Étape 2: Compression progressive selon l'objectif de taille
            $objectifTailleBytes = self::MAX_FILE_SIZE_KB * 1024;
            $qualiteActuelle = ($typeReel === 'image/jpeg') ? self::JPEG_QUALITY_START : self::WEBP_QUALITY_START;
            $qualiteMin = ($typeReel === 'image/jpeg') ? self::JPEG_QUALITY_MIN : self::WEBP_QUALITY_MIN;
            
            $tentatives = 0;
            $maxTentatives = 10;
            
            do {
                $tentatives++;
                $fichierTemp = $cheminFichier . '.temp';
                
                // Sauvegarder avec la qualité actuelle
                $succes = false;
                switch ($typeReel) {
                    case 'image/jpeg':
                        $succes = imagejpeg($image, $fichierTemp, $qualiteActuelle);
                        break;
                    case 'image/png':
                        // PNG: convertir en JPEG si trop gros (sauf si transparence nécessaire)
                        if ($tailleOriginale > $objectifTailleBytes * 2) {
                            $succes = imagejpeg($image, $fichierTemp, $qualiteActuelle);
                            $typeReel = 'image/jpeg'; // Changer le type
                            $details['etapes'][] = "Conversion PNG → JPEG pour meilleure compression";
                        } else {
                            $niveauCompression = min(9, max(0, round(9 - ($qualiteActuelle / 10))));
                            $succes = imagepng($image, $fichierTemp, $niveauCompression);
                        }
                        break;
                    case 'image/gif':
                        // GIF: convertir en JPEG pour une meilleure compression
                        $succes = imagejpeg($image, $fichierTemp, $qualiteActuelle);
                        $typeReel = 'image/jpeg';
                        $details['etapes'][] = "Conversion GIF → JPEG pour meilleure compression";
                        break;
                    case 'image/webp':
                        $succes = imagewebp($image, $fichierTemp, $qualiteActuelle);
                        break;
                }
                
                if (!$succes) {
                    throw new Exception('Erreur lors de la sauvegarde');
                }
                
                $tailleActuelle = filesize($fichierTemp);
                $details['etapes'][] = "Tentative {$tentatives}: qualité {$qualiteActuelle}% = " . $this->formatBytes($tailleActuelle);
                
                // Si la taille est acceptable, on garde ce fichier
                if ($tailleActuelle <= $objectifTailleBytes || $qualiteActuelle <= $qualiteMin) {
                    rename($fichierTemp, $cheminFichier);
                    break;
                }
                
                // Sinon, on réduit la qualité
                unlink($fichierTemp);
                $qualiteActuelle = max($qualiteMin, $qualiteActuelle - 10);
                
            } while ($tentatives < $maxTentatives);
            
            imagedestroy($image);
            
            $tailleFinale = filesize($cheminFichier);
            $details['taille_finale'] = $this->formatBytes($tailleFinale);
            $details['reduction'] = round((($tailleOriginale - $tailleFinale) / $tailleOriginale) * 100, 1) . '%';
            
            return [true, $details];
            
        } catch (Exception $e) {
            Log::error('Erreur compression image: ' . $e->getMessage());
            return [false, ['erreur' => $e->getMessage()]];
        }
    }

    /**
     * Compression PDF (nécessite Ghostscript)
     */
    private function compressPDF($cheminFichier)
    {
        try {
            $details = ['etapes' => []];
            $tailleOriginale = filesize($cheminFichier);
            $details['etapes'][] = "PDF original: " . $this->formatBytes($tailleOriginale);
            
            // Vérifier si Ghostscript est disponible avec diagnostic
            $gsCommand = $this->findGhostscriptCommand();
            if (!$gsCommand) {
                // Test de diagnostic plus poussé avec nettoyage UTF-8
                $testCommands = ['gswin64', 'gswin64c', 'gs'];
                $diagnosticInfo = [];
                
                foreach ($testCommands as $testCmd) {
                    $output = [];
                    $returnCode = 0;
                    exec("$testCmd --version 2>&1", $output, $returnCode);
                    
                    // Nettoyer la sortie pour éviter les problèmes UTF-8
                    $cleanOutput = array_map([$this, 'cleanUtf8String'], $output);
                    
                    $diagnosticInfo[$testCmd] = [
                        'return_code' => $returnCode,
                        'output' => $this->cleanUtf8String(implode(' ', $cleanOutput))
                    ];
                }
                
                $details['etapes'][] = "Ghostscript non disponible";
                $details['diagnostic_ghostscript'] = $diagnosticInfo;
                return [false, $details];
            }
            
            $details['etapes'][] = "Ghostscript détecté: $gsCommand";
            
            $fichierTemp = $cheminFichier . '.compressed.pdf';
            
            // Commande Ghostscript pour compression avec gestion d'erreurs Windows
            $command = sprintf(
                '"%s" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile="%s" "%s"',
                $gsCommand,
                $fichierTemp,
                $cheminFichier
            );
            
            $details['etapes'][] = "Commande préparée";
            
            $output = [];
            $returnCode = 0;
            
            // Rediriger les erreurs vers stdout pour capturer tout
            exec($command . ' 2>&1', $output, $returnCode);
            
            // Nettoyer la sortie pour éviter les problèmes UTF-8
            $cleanOutput = array_map([$this, 'cleanUtf8String'], $output);
            
            $details['etapes'][] = "Code retour: $returnCode";
            if (!empty($cleanOutput)) {
                $details['etapes'][] = "Sortie: " . $this->cleanUtf8String(implode(' ', $cleanOutput));
            }
            
            if ($returnCode === 0 && file_exists($fichierTemp)) {
                $tailleCompresse = filesize($fichierTemp);
                
                // Ne garder la version compressée que si elle est plus petite
                if ($tailleCompresse < $tailleOriginale && $tailleCompresse > 0) {
                    rename($fichierTemp, $cheminFichier);
                    $details['etapes'][] = "PDF compressé: " . $this->formatBytes($tailleCompresse);
                    $details['reduction'] = round((($tailleOriginale - $tailleCompresse) / $tailleOriginale) * 100, 1) . '%';
                    return [true, $details];
                } else {
                    if (file_exists($fichierTemp)) {
                        unlink($fichierTemp);
                    }
                    $details['etapes'][] = "Compression PDF sans gain, fichier original conservé";
                    $details['etapes'][] = "Taille originale: " . $this->formatBytes($tailleOriginale) . ", Taille compressée: " . $this->formatBytes($tailleCompresse);
                    return [false, $details];
                }
            } else {
                $details['etapes'][] = "Erreur lors de la compression PDF";
                $details['etapes'][] = "Fichier temporaire existe: " . (file_exists($fichierTemp) ? 'Oui' : 'Non');
                if (file_exists($fichierTemp)) {
                    unlink($fichierTemp); // Nettoyer le fichier temporaire
                }
                return [false, $details];
            }
            
        } catch (Exception $e) {
            Log::error('Erreur compression PDF: ' . $e->getMessage());
            return [false, ['erreur' => $this->cleanUtf8String($e->getMessage())]];
        }
    }

    /**
     * Trouve la commande Ghostscript disponible
     */
    private function findGhostscriptCommand()
    {
        // Commandes possibles selon l'OS
        if (PHP_OS_FAMILY === 'Windows') {
            $possibleCommands = [
                'gswin64c',
                'gswin32c',
                '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"',
                '"C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe"',
                '"C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe"',
            ];
        } else {
            $possibleCommands = ['gs'];
        }
        
        foreach ($possibleCommands as $cmd) {
            $output = [];
            $returnCode = 0;
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Pour Windows : ghostscript s'exécute mais attend des commandes
                // On lui donne une commande quit immédiatement
                $testCommand = "echo quit | $cmd 2>nul";
                exec($testCommand, $output, $returnCode);
                
                // Si on obtient une sortie contenant "GPL Ghostscript", c'est bon
                $outputString = implode(' ', $output);
                if (strpos($outputString, 'GPL Ghostscript') !== false || 
                    strpos($outputString, 'Ghostscript') !== false) {
                    return $cmd;
                }
            } else {
                // Pour Unix/Linux, utiliser --version
                exec("$cmd --version 2>/dev/null", $output, $returnCode);
                if ($returnCode === 0 && !empty($output)) {
                    return $cmd;
                }
            }
        }
        
        return null;
    }

    /**
     * Nettoie une chaîne pour éviter les problèmes d'encodage JSON
     */
    private function cleanUtf8String($string)
    {
        // Convertir en UTF-8 et supprimer les caractères invalides
        $cleaned = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $cleaned = mb_substitute_character(0x0020); // Remplacer par espace
        $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'UTF-8');
        
        // Supprimer les caractères de contrôle sauf \n et \t
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Formatage des tailles de fichiers
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}