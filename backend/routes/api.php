<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\BatimentController;
use App\Http\Controllers\Api\NiveauController;
use App\Http\Controllers\Api\PartieController;
use App\Http\Controllers\Api\LotController;
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

// Routes protégées (avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // Routes pour la gestion des sites et bâtiments
    Route::apiResource('sites', SiteController::class);
    Route::apiResource('batiments', BatimentController::class);
    Route::apiResource('niveaux', NiveauController::class);
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
    
    // Routes pour les parties d'un niveau spécifique
    Route::get('niveaux/{niveau}/parties', function ($niveauId) {
        return app(PartieController::class)->index(request()->merge(['niveau_id' => $niveauId]));
    });
    
    // Routes pour la gestion des lots dans les parties
    Route::post('parties/{partie}/lots/attach', [PartieController::class, 'attachLot']);
    Route::post('parties/{partie}/lots/detach', [PartieController::class, 'detachLot']);
    
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