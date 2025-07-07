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
        
        // 2. Typologies de référence (ERP, IGH, HAB, BUP)
        $this->command->info('🏗️ Création des typologies de référence...');
        $this->call(TypologiesSeeder::class);
        
        // 3. Utilisateurs avec rôles
        $this->command->info('👥 Création des utilisateurs...');
        $this->call(UsersSeeder::class);
        
        // 4. Sites
        $this->command->info('🏢 Création des sites...');
        $this->call(SitesSeeder::class);
        
        // 5. Bâtiments, niveaux, parties et typologies
        $this->command->info('🏗️ Création des bâtiments et structures...');
        $this->call(BatimentsSeeder::class);
        
        // 6. Droits d'accès granulaires
        $this->command->info('🔐 Attribution des droits d\'accès...');
        $this->call(DroitsSeeder::class);
        
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('');
        $this->command->info('🎯 Base de données prête pour les tests');
        $this->command->info('📊 Données créées :');
        $this->command->info('   - 7 utilisateurs avec rôles différents');
        $this->command->info('   - 8 sites avec typologies variées');
        $this->command->info('   - ~20 bâtiments (ERP, HAB, IGH, BUP, ICPE)');
        $this->command->info('   - Niveaux et parties détaillés');
        $this->command->info('   - Droits d\'accès granulaires');
        $this->command->info('   - Typologies réglementaires complètes');
    }
}