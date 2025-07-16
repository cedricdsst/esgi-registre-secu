<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Partie;
use App\Models\Site;
use App\Models\Batiment;
use App\Models\Niveau;

class TestOwnershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trouver Michel Leroy
        $michelLeroy = User::where('email', 'michel.leroy@cleanpro.com')->first();
        
        if (!$michelLeroy) {
            $this->command->error('Michel Leroy non trouvé. Exécutez d\'abord UsersSeeder.');
            return;
        }

        // Créer des données de test si elles n'existent pas
        $this->createTestData();

        // Assigner Michel Leroy à quelques parties
        $this->assignPartiestoMichelLeroy($michelLeroy);

        // Assigner d'autres utilisateurs pour les tests
        $this->assignOtherUsers();

        $this->command->info('Assignations de test créées avec succès !');
        $this->command->info('Michel Leroy (michel.leroy@cleanpro.com) a été assigné à des parties pour test.');
    }

    private function createTestData()
    {
        // Créer un site de test s'il n'existe pas
        $testSite = Site::firstOrCreate([
            'nom' => 'Site de Test'
        ], [
            'adresse' => '123 Rue de Test',
            'code_postal' => '75001',
            'ville' => 'Paris',
            'pays' => 'France',
            'description' => 'Site créé pour les tests',
            'client_id' => 1 // ID du super admin
        ]);

        // Créer un bâtiment de test
        $testBatiment = Batiment::firstOrCreate([
            'site_id' => $testSite->id,
            'name' => 'Bâtiment Test A'
        ], [
            'type' => 'ERP',
            'isICPE' => false
        ]);

        // Créer des niveaux de test
        $niveauRdc = Niveau::firstOrCreate([
            'batiment_id' => $testBatiment->id,
            'nom' => 'Rez-de-chaussée'
        ], [
            'numero_etage' => 0,
            'description' => 'Niveau rez-de-chaussée de test'
        ]);

        $niveau1 = Niveau::firstOrCreate([
            'batiment_id' => $testBatiment->id,
            'nom' => '1er étage'
        ], [
            'numero_etage' => 1,
            'description' => 'Premier étage de test'
        ]);

        // Créer des parties de test
        $partie1 = Partie::firstOrCreate([
            'batiment_id' => $testBatiment->id,
            'nom' => 'Hall d\'entrée'
        ], [
            'type' => 'commune',
            'isICPE' => false,
            'isPrivative' => false
        ]);

        $partie2 = Partie::firstOrCreate([
            'batiment_id' => $testBatiment->id,
            'nom' => 'Bureau 101'
        ], [
            'type' => 'privative',
            'isICPE' => false,
            'isPrivative' => true
        ]);

        $partie3 = Partie::firstOrCreate([
            'batiment_id' => $testBatiment->id,
            'nom' => 'Escalier principal'
        ], [
            'type' => 'commune',
            'isICPE' => false,
            'isPrivative' => false
        ]);

        // Associer les parties aux niveaux
        $partie1->niveaux()->syncWithoutDetaching([$niveauRdc->id]);
        $partie2->niveaux()->syncWithoutDetaching([$niveauRdc->id]);
        $partie3->niveaux()->syncWithoutDetaching([$niveauRdc->id, $niveau1->id]);

        $this->command->info('Données de test créées/vérifiées.');
    }

    private function assignPartiestoMichelLeroy($michelLeroy)
    {
        // Récupérer quelques parties pour les assigner à Michel Leroy
        $parties = Partie::limit(3)->get();

        foreach ($parties as $partie) {
            $partie->owner_id = $michelLeroy->id;
            $partie->save();
        }

        $this->command->info("Assigné {$parties->count()} parties à Michel Leroy.");
    }

    private function assignOtherUsers()
    {
        // Assigner d'autres utilisateurs entreprise à des parties
        $sylvieDubois = User::where('email', 'sylvie.dubois@maintenance-plus.fr')->first();
        $carlosGarcia = User::where('email', 'carlos.garcia@syndic-moderne.fr')->first();

        if ($sylvieDubois) {
            $partiesSylvie = Partie::where('owner_id', null)->limit(2)->get();
            foreach ($partiesSylvie as $partie) {
                $partie->owner_id = $sylvieDubois->id;
                $partie->save();
            }
            $this->command->info("Assigné {$partiesSylvie->count()} parties à Sylvie Dubois.");
        }

        if ($carlosGarcia) {
            $partiesCarlos = Partie::where('owner_id', null)->limit(2)->get();
            foreach ($partiesCarlos as $partie) {
                $partie->owner_id = $carlosGarcia->id;
                $partie->save();
            }
            $this->command->info("Assigné {$partiesCarlos->count()} parties à Carlos Garcia.");
        }
    }
} 