<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Début du seeding de la base de données AXIGNIS...');
        
        // 1. Rôles et permissions Spatie (obligatoire en premier)
        $this->command->info('📋 Création des rôles et permissions...');
        $this->call(RolesAndPermissionsSeeder::class);
        
        // 2. Création du super admin uniquement
        $this->command->info('👥 Création du super admin...');
        $this->call(UsersSeeder::class);
        
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('');
        $this->command->info('🎯 Base de données prête pour utilisation');
        $this->command->info('📊 Données créées :');
        $this->command->info('   - Rôles et permissions système');
        $this->command->info('   - 1 Super admin');
        $this->command->info('   - 2 Utilisateurs entreprise');
        $this->command->info('   - 2 Utilisateurs intervenants');
        $this->command->info('');
        $this->command->info('ℹ️  Toutes les autres données (sites, bâtiments, clients...) doivent être créées via l\'interface d\'administration.');
    }
}