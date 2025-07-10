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
        
        // 7. Types d'interventions
        $this->command->info('⚙️ Création des types d\'interventions...');
        $this->call(TypesInterventionsSeeder::class);
        
        // 8. Entreprises
        $this->command->info('🏢 Création des entreprises...');
        $this->call(EntreprisesSeeder::class);
        
        // 9. Types de rapports
        $this->command->info('📋 Création des types de rapports...');
        $this->call(TypesRapportsSeeder::class);
        
        // 10. Interventions
        $this->command->info('🔧 Création des interventions...');
        $this->call(InterventionsSeeder::class);
        
        // 11. Rapports
        $this->command->info('📄 Création des rapports...');
        $this->call(RapportsSeeder::class);
        
        // 12. Observations
        $this->command->info('👁️ Création des observations...');
        $this->call(ObservationsSeeder::class);
        
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
        $this->command->info('   - 3 types d\'interventions par défaut');
        $this->command->info('   - 10 entreprises avec organismes agréés');
        $this->command->info('   - 16 types de rapports réglementaires');
        $this->command->info('   - Interventions d\'exemple avec différents statuts');
        $this->command->info('   - Rapports liés aux interventions');
        $this->command->info('   - Observations détaillées avec priorités');
    }
}