<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Routes publiques (sans authentification)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Routes protégées (avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // Futures routes pour vos applications
    Route::prefix('security-register')->group(function () {
        // Routes pour l'application registre de sécurité
    });
    
    Route::prefix('equipment-base')->group(function () {
        // Routes pour l'application base d'équipements
    });
});