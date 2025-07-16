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

        // Utilisateurs entreprise (2)
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

        // Utilisateurs intervenants (2)
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

        $this->command->info('Utilisateurs créés avec succès !');
        $this->command->info('');
        $this->command->info('=== COMPTE ADMINISTRATEUR ===');
        $this->command->info('- Super Admin: admin@axignis.com / password123');
        $this->command->info('');
        $this->command->info('=== COMPTES ENTREPRISES (user-entreprise) ===');
        $this->command->info('- Entreprise Nettoyage: michel.leroy@cleanpro.com / password123');
        $this->command->info('- Entreprise Maintenance: sylvie.dubois@maintenance-plus.fr / password123');
        $this->command->info('');
        $this->command->info('=== COMPTES INTERVENANTS (user-intervenant) ===');
        $this->command->info('- Technicien Sécurité: thomas.rousseau@securitech.com / password123');
        $this->command->info('- Contrôleur Technique: celine.lambert@controle-securite.fr / password123');
        $this->command->info('');
        $this->command->info('ℹ️  Tous les autres utilisateurs doivent être créés via l\'interface d\'administration.');
    }
}
