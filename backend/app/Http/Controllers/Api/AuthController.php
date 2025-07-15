<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
// use App\Models\PasswordSetupToken; // Non utilisé - redirection directe vers /login
use App\Notifications\AdminUserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Connexion utilisateur
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'Le format de l\'email est invalide.',
            'password.required' => 'Le mot de passe est requis.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Aucun utilisateur trouvé avec cet email.'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Votre email n\'est pas vérifié.'
            ], 403);
        }

        // Créer un token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'organisation' => $user->organisation,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Inscription utilisateur
     */
    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organisation' => 'required|string|max:255',
            'role' => 'sometimes|string|in:user,admin,super-admin',
        ], [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'organisation.required' => 'L\'organisation est requise.',
        ]);

        try {
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'organisation' => $request->organisation,
                'role' => $request->role ?? 'user',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Assigner le rôle Spatie
        $user->assignRole($request->role ?? 'user');

        // Envoi de l'email de vérification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'organisation' => $user->organisation,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Informations utilisateur connecté
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'organisation' => $user->organisation,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Déconnexion de tous les appareils
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion de tous les appareils réussie'
        ]);
    }

    /**
     * Inscription d'un utilisateur par le super admin
     */
    public function adminRegister(Request $request)
    {
        // Vérifier que l'utilisateur connecté est super-admin
        if (!$request->user()->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Accès interdit. Seuls les super-admins peuvent créer des comptes utilisateurs.'
            ], 403);
        }

        try {
            Log::info('Début création utilisateur par admin', [
                'admin_id' => $request->user()->id,
                'data' => $request->except(['password'])
            ]);

            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'organisation' => 'required|string|max:255',
                'role' => 'required|string|in:super-admin,admin,client-admin,user,viewer,user-entreprise,user-intervenant',
            ], [
                'nom.required' => 'Le nom est requis.',
                'prenom.required' => 'Le prénom est requis.',
                'email.required' => 'L\'email est requis.',
                'email.email' => 'Le format de l\'email est invalide.',
                'email.unique' => 'Cet email est déjà utilisé.',
                'organisation.required' => 'L\'organisation est requise.',
                'role.required' => 'Le rôle est requis.',
                'role.in' => 'Le rôle doit être user, admin ou super-admin.',
            ]);

            // Générer un mot de passe temporaire lisible
            $temporaryPassword = 'Temp' . rand(1000, 9999) . '!';
            
            Log::info('Création utilisateur en cours', [
                'email' => $request->email,
                'role' => $request->role
            ]);
            
            // Créer l'utilisateur avec le mot de passe temporaire
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($temporaryPassword),
                'organisation' => $request->organisation,
                'role' => $request->role,
                'email_verified_at' => now(), // Email vérifié par défaut pour les comptes admin
            ]);

            Log::info('Utilisateur créé avec succès', ['user_id' => $user->id]);

            // Assigner le rôle Spatie
            $user->assignRole($request->role);

            // Créer un token de validation simple (pour sécuriser le lien)
            // Envoyer l'email avec le mot de passe temporaire
            $adminName = $request->user()->prenom . ' ' . $request->user()->nom;
            $emailSent = false;
            
            try {
                $user->notify(new AdminUserCreated(null, $adminName, $temporaryPassword));
                $emailSent = true;
                Log::info('Email envoyé avec succès', ['user_id' => $user->id]);
            } catch (\Exception $emailError) {
                // Log l'erreur email mais ne pas faire échouer la création
                Log::error('Erreur envoi email pour utilisateur créé: ' . $emailError->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $emailError->getMessage()
                ]);
            }

            $message = $emailSent 
                ? 'Utilisateur créé avec succès. Un email a été envoyé avec les informations de connexion.'
                : 'Utilisateur créé avec succès. Erreur lors de l\'envoi de l\'email - mot de passe temporaire: ' . $temporaryPassword;

            $response = [
                'message' => $message,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'organisation' => $user->organisation,
                    'created_at' => $user->created_at->toISOString(),
                    'created_by' => $adminName,
                ]
            ];

            // Ajouter le mot de passe temporaire seulement si l'email a échoué
            if (!$emailSent) {
                $response['temporary_password'] = $temporaryPassword;
            }

            Log::info('Réponse prête à être envoyée', ['response' => $response]);

            return response()->json($response, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erreur de validation lors de la création utilisateur', [
                'errors' => $e->errors(),
                'admin_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création utilisateur par admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Note: Méthode validateWelcomeToken supprimée - redirection directe vers /login

    /**
     * Récupérer la liste de tous les utilisateurs (super-admin uniquement)
     */
    public function getAllUsers(Request $request)
    {
        // Vérifier que l'utilisateur connecté est super-admin
        if (!$request->user()->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Accès interdit. Seuls les super-admins peuvent consulter la liste des utilisateurs.'
            ], 403);
        }

        try {
            $users = User::select('id', 'nom', 'prenom', 'email', 'role', 'organisation', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des utilisateurs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}