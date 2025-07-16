<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\BatimentController;
use App\Http\Controllers\Api\NiveauController;
use App\Http\Controllers\Api\PartieController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\EntrepriseController;
use App\Http\Controllers\Api\TypeInterventionController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\TypeRapportController;
use App\Http\Controllers\Api\RapportController;
use App\Http\Controllers\Api\ObservationController;
use App\Http\Controllers\Api\InventairePartieController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Routes publiques (sans authentification)
Route::get('/', function () {
    return view('welcome');
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Note: Token de bienvenue supprimé, redirection directe vers /login

// Routes protégées (avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // Route pour l'inscription par super admin
    Route::post('/admin/register', [AuthController::class, 'adminRegister']);
    
    // Route pour récupérer la liste des utilisateurs (super-admin uniquement)
    Route::get('/admin/users', [AuthController::class, 'getAllUsers']);
    
    // Routes pour la gestion des sites et bâtiments
    Route::apiResource('sites', SiteController::class);
    Route::apiResource('batiments', BatimentController::class);
    Route::apiResource('niveaux', NiveauController::class);
    
    // Routes spécifiques pour les parties (AVANT la route resource)
    Route::get('parties/entreprise-users', [PartieController::class, 'getEntrepriseUsers']);
    Route::post('parties/{partie}/lots/attach', [PartieController::class, 'attachLot']);
    Route::post('parties/{partie}/lots/detach', [PartieController::class, 'detachLot']);
    Route::post('parties/{partie}/lots/transfer', [PartieController::class, 'transferLot']);
    Route::post('parties/{partie}/assign-owner', [PartieController::class, 'assignOwner']);
    Route::post('parties/assign-owner-bulk', [PartieController::class, 'assignOwnerBulk']);
    
    // Route resource pour les parties (APRÈS les routes spécifiques)
    Route::apiResource('parties', PartieController::class)->parameters(['parties' => 'partie']);
    Route::apiResource('lots', LotController::class);
    
    // Routes pour les bâtiments d'un site spécifique
    Route::get('sites/{site}/batiments', function ($siteId) {
        return app(BatimentController::class)->index(request()->merge(['site_id' => $siteId]));
    });
    
    // Routes pour les niveaux d'un bâtiment spécifique
    Route::get('batiments/{batiment}/niveaux', function ($batimentId) {
        return app(NiveauController::class)->index(request()->merge(['batiment_id' => $batimentId]));
    });
    
    // Routes pour les parties d'un bâtiment spécifique (nouvelle structure)
    Route::get('batiments/{batiment}/parties', function ($batimentId) {
        return app(PartieController::class)->index(request()->merge(['batiment_id' => $batimentId]));
    });
    
    // Routes pour les parties d'un niveau spécifique
    Route::get('niveaux/{niveau}/parties', function ($niveauId) {
        return app(PartieController::class)->index(request()->merge(['niveau_id' => $niveauId]));
    });
    
    // Routes pour la gestion des propriétaires de parties
    Route::get('batiments/{batiment}/parties-with-owners', [PartieController::class, 'getPartiesByBatimentWithOwners']);
    
    // Routes pour les entreprises
    Route::apiResource('entreprises', EntrepriseController::class);
    
    // Routes pour les types d'interventions
    Route::apiResource('types-interventions', TypeInterventionController::class);
    
    // Routes pour les interventions
    Route::get('interventions/intervenants', [InterventionController::class, 'getIntervenantUsers']);
    Route::apiResource('interventions', InterventionController::class);
    Route::put('interventions/{intervention}/status', [InterventionController::class, 'updateStatus']);
    Route::post('interventions/{intervention}/sign', [InterventionController::class, 'sign']);
    
    // Routes pour les types de rapports
    Route::apiResource('types-rapports', TypeRapportController::class);
    
    // Routes pour les rapports
    Route::apiResource('rapports', RapportController::class);
    Route::post('rapports/{rapport}/sign', [RapportController::class, 'sign']);
    Route::post('rapports/{rapport}/archive', [RapportController::class, 'archive']);
    
    // Routes pour les fichiers de rapports
    Route::post('rapports/{rapport}/files', [RapportController::class, 'uploadFile']);
    Route::get('rapports/{rapport}/files/{fichier_id}', [RapportController::class, 'downloadFile']);
    Route::delete('rapports/{rapport}/files/{fichier_id}', [RapportController::class, 'deleteFile']);
    
    // Routes pour les observations
    Route::apiResource('observations', ObservationController::class);
    Route::post('observations/create-follow-up-intervention', [ObservationController::class, 'createFollowUpIntervention']);
    
    // Routes pour les fichiers d'observations
    Route::post('observations/{observation}/files', [ObservationController::class, 'uploadFile']);
    Route::get('observations/{observation}/files/{fichier_id}', [ObservationController::class, 'downloadFile']);
    Route::delete('observations/{observation}/files/{fichier_id}', [ObservationController::class, 'deleteFile']);
    
    // Routes pour les inventaires par partie
    Route::apiResource('inventaires-partie', InventairePartieController::class);
    Route::get('parties/{partie}/inventaires', [InventairePartieController::class, 'getByPartie']);
    Route::post('inventaires-partie/{inventairePartie}/sync', [InventairePartieController::class, 'syncWithApi']);
    
    // Futures routes pour vos applications
    Route::prefix('security-register')->group(function () {
        // Routes pour l'application registre de sécurité
    });
    
    Route::prefix('equipment-base')->group(function () {
        // Routes pour l'application base d'équipements
    });
});

// Envoi du lien de vérification (pour renvoyer le mail)
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Lien de vérification envoyé !']);
})->middleware('auth:sanctum');

// Vérification du lien (clic sur le lien reçu par email)
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Lien de vérification invalide.'], 400);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email déjà vérifié.'], 200);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email vérifié avec succès !']);
})->middleware(['signed'])->name('verification.verify');