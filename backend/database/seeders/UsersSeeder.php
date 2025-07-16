<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super administrateur AXIGNIS
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@axignis.com'],
            [
                'nom' => 'Admin',
                'prenom' => 'Super',
                'password' => Hash::make('password123'),
                'role' => 'super-admin',
                'organisation' => 'AXIGNIS',
                'email_verified_at' => now(),
            ]
        );
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        // Administrateur AXIGNIS
        $admin = User::firstOrCreate(
            ['email' => 'jean.dupont@axignis.com'],
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'organisation' => 'AXIGNIS',
                'email_verified_at' => now(),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Utilisateur interne AXIGNIS
        $userAxignis = User::firstOrCreate(
            ['email' => 'sophie.martin@axignis.com'],
            [
                'nom' => 'Martin',
                'prenom' => 'Sophie',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'organisation' => 'AXIGNIS',
                'email_verified_at' => now(),
            ]
        );
        if (!$userAxignis->hasRole('user')) {
            $userAxignis->assignRole('user');
        }

        // Clients externes
        $clientHotel = User::firstOrCreate(
            ['email' => 'pierre.bernard@hotel-grand-paris.com'],
            [
                'nom' => 'Bernard',
                'prenom' => 'Pierre',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'organisation' => 'Hôtel Grand Paris',
                'email_verified_at' => now(),
            ]
        );
        if (!$clientHotel->hasRole('user')) {
            $clientHotel->assignRole('user');
        }

        $clientCopropriete = User::firstOrCreate(
            ['email' => 'marie.durand@syndic-paris.com'],
            [
                'nom' => 'Durand',
                'prenom' => 'Marie',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'organisation' => 'Syndic Paris Centre',
                'email_verified_at' => now(),
            ]
        );
        if (!$clientCopropriete->hasRole('user')) {
            $clientCopropriete->assignRole('user');
        }

        $clientEntreprise = User::firstOrCreate(
            ['email' => 'luc.moreau@techno-industries.com'],
            [
                'nom' => 'Moreau',
                'prenom' => 'Luc',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'organisation' => 'Techno Industries',
                'email_verified_at' => now(),
            ]
        );
        if (!$clientEntreprise->hasRole('user')) {
            $clientEntreprise->assignRole('user');
        }

        $clientEcole = User::firstOrCreate(
            ['email' => 'anne.petit@lycee-voltaire.edu'],
            [
                'nom' => 'Petit',
                'prenom' => 'Anne',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'organisation' => 'Lycée Voltaire',
                'email_verified_at' => now(),
            ]
        );
        if (!$clientEcole->hasRole('user')) {
            $clientEcole->assignRole('user');
        }

        // === NOUVEAUX UTILISATEURS POUR TESTS ===

        // Utilisateur Entreprise 1 - Entreprise de nettoyage
        $userEntreprise1 = User::firstOrCreate(
            ['email' => 'michel.leroy@cleanpro.com'],
            [
                'nom' => 'Leroy',
                'prenom' => 'Michel',
                'password' => Hash::make('password123'),
                'role' => 'user-entreprise',
                'organisation' => 'CleanPro Services',
                'email_verified_at' => now(),
            ]
        );
        if (!$userEntreprise1->hasRole('user-entreprise')) {
            $userEntreprise1->assignRole('user-entreprise');
        }

        // Utilisateur Entreprise 2 - Entreprise de maintenance
        $userEntreprise2 = User::firstOrCreate(
            ['email' => 'sylvie.dubois@maintenance-plus.fr'],
            [
                'nom' => 'Dubois',
                'prenom' => 'Sylvie',
                'password' => Hash::make('password123'),
                'role' => 'user-entreprise',
                'organisation' => 'Maintenance Plus',
                'email_verified_at' => now(),
            ]
        );
        if (!$userEntreprise2->hasRole('user-entreprise')) {
            $userEntreprise2->assignRole('user-entreprise');
        }

        // Utilisateur Entreprise 3 - Syndic de copropriété
        $userEntreprise3 = User::firstOrCreate(
            ['email' => 'carlos.garcia@syndic-moderne.fr'],
            [
                'nom' => 'Garcia',
                'prenom' => 'Carlos',
                'password' => Hash::make('password123'),
                'role' => 'user-entreprise',
                'organisation' => 'Syndic Moderne',
                'email_verified_at' => now(),
            ]
        );
        if (!$userEntreprise3->hasRole('user-entreprise')) {
            $userEntreprise3->assignRole('user-entreprise');
        }

        // Utilisateur Intervenant 1 - Technicien sécurité incendie
        $userIntervenant1 = User::firstOrCreate(
            ['email' => 'thomas.rousseau@securitech.com'],
            [
                'nom' => 'Rousseau',
                'prenom' => 'Thomas',
                'password' => Hash::make('password123'),
                'role' => 'user-intervenant',
                'organisation' => 'SecuriTech',
                'email_verified_at' => now(),
            ]
        );
        if (!$userIntervenant1->hasRole('user-intervenant')) {
            $userIntervenant1->assignRole('user-intervenant');
        }

        // Utilisateur Intervenant 2 - Contrôleur technique
        $userIntervenant2 = User::firstOrCreate(
            ['email' => 'celine.lambert@controle-securite.fr'],
            [
                'nom' => 'Lambert',
                'prenom' => 'Céline',
                'password' => Hash::make('password123'),
                'role' => 'user-intervenant',
                'organisation' => 'Contrôle Sécurité France',
                'email_verified_at' => now(),
            ]
        );
        if (!$userIntervenant2->hasRole('user-intervenant')) {
            $userIntervenant2->assignRole('user-intervenant');
        }

        // Utilisateur Intervenant 3 - Expert en ascenseurs
        $userIntervenant3 = User::firstOrCreate(
            ['email' => 'antoine.fabre@ascenseurs-expert.com'],
            [
                'nom' => 'Fabre',
                'prenom' => 'Antoine',
                'password' => Hash::make('password123'),
                'role' => 'user-intervenant',
                'organisation' => 'Ascenseurs Expert',
                'email_verified_at' => now(),
            ]
        );
        if (!$userIntervenant3->hasRole('user-intervenant')) {
            $userIntervenant3->assignRole('user-intervenant');
        }

        $this->command->info('Utilisateurs créés avec succès !');
        $this->command->info('');
        $this->command->info('=== COMPTES ADMINISTRATEURS ===');
        $this->command->info('- Super Admin: admin@axignis.com / password123');
        $this->command->info('- Admin: jean.dupont@axignis.com / password123');
        $this->command->info('- User AXIGNIS: sophie.martin@axignis.com / password123');
        $this->command->info('');
        $this->command->info('=== COMPTES CLIENTS ===');
        $this->command->info('- Client Hôtel: pierre.bernard@hotel-grand-paris.com / password123');
        $this->command->info('- Client Copropriété: marie.durand@syndic-paris.com / password123');
        $this->command->info('- Client Entreprise: luc.moreau@techno-industries.com / password123');
        $this->command->info('- Client École: anne.petit@lycee-voltaire.edu / password123');
        $this->command->info('');
        $this->command->info('=== COMPTES ENTREPRISES (user-entreprise) ===');
        $this->command->info('- Entreprise Nettoyage: michel.leroy@cleanpro.com / password123');
        $this->command->info('- Entreprise Maintenance: sylvie.dubois@maintenance-plus.fr / password123');
        $this->command->info('- Syndic de Copropriété: carlos.garcia@syndic-moderne.fr / password123');
        $this->command->info('');
        $this->command->info('=== COMPTES INTERVENANTS (user-intervenant) ===');
        $this->command->info('- Technicien Sécurité: thomas.rousseau@securitech.com / password123');
        $this->command->info('- Contrôleur Technique: celine.lambert@controle-securite.fr / password123');
        $this->command->info('- Expert Ascenseurs: antoine.fabre@ascenseurs-expert.com / password123');
    }
}
