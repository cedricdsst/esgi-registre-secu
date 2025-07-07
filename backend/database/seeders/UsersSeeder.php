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
        $superAdmin = User::create([
            'nom' => 'Admin',
            'prenom' => 'Super',
            'email' => 'admin@axignis.com',
            'password' => Hash::make('password123'),
            'role' => 'super-admin',
            'organisation' => 'AXIGNIS',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Administrateur AXIGNIS
        $admin = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@axignis.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'organisation' => 'AXIGNIS',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Utilisateur interne AXIGNIS
        $userAxignis = User::create([
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'sophie.martin@axignis.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'organisation' => 'AXIGNIS',
            'email_verified_at' => now(),
        ]);
        $userAxignis->assignRole('user');

        // Clients externes
        $clientHotel = User::create([
            'nom' => 'Bernard',
            'prenom' => 'Pierre',
            'email' => 'pierre.bernard@hotel-grand-paris.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'organisation' => 'Hôtel Grand Paris',
            'email_verified_at' => now(),
        ]);
        $clientHotel->assignRole('user');

        $clientCopropriete = User::create([
            'nom' => 'Durand',
            'prenom' => 'Marie',
            'email' => 'marie.durand@syndic-paris.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'organisation' => 'Syndic Paris Centre',
            'email_verified_at' => now(),
        ]);
        $clientCopropriete->assignRole('user');

        $clientEntreprise = User::create([
            'nom' => 'Moreau',
            'prenom' => 'Luc',
            'email' => 'luc.moreau@techno-industries.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'organisation' => 'Techno Industries',
            'email_verified_at' => now(),
        ]);
        $clientEntreprise->assignRole('user');

        $clientEcole = User::create([
            'nom' => 'Petit',
            'prenom' => 'Anne',
            'email' => 'anne.petit@lycee-voltaire.edu',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'organisation' => 'Lycée Voltaire',
            'email_verified_at' => now(),
        ]);
        $clientEcole->assignRole('user');

        $this->command->info('Utilisateurs créés avec succès !');
        $this->command->info('Comptes de test :');
        $this->command->info('- Super Admin: admin@axignis.com / password123');
        $this->command->info('- Admin: jean.dupont@axignis.com / password123');
        $this->command->info('- User AXIGNIS: sophie.martin@axignis.com / password123');
        $this->command->info('- Client Hôtel: pierre.bernard@hotel-grand-paris.com / password123');
        $this->command->info('- Client Copropriété: marie.durand@syndic-paris.com / password123');
        $this->command->info('- Client Entreprise: luc.moreau@techno-industries.com / password123');
        $this->command->info('- Client École: anne.petit@lycee-voltaire.edu / password123');
    }
}
