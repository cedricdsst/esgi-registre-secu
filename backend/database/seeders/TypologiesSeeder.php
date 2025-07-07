<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ErpType;
use App\Models\ErpCategory;
use App\Models\IghClass;
use App\Models\HabFamille;

class TypologiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Types ERP selon la réglementation française
        $erpTypes = [
            ['tag' => 'J', 'name' => 'Structures d\'accueil pour personnes âgées et personnes handicapées', 'isSpecial' => false],
            ['tag' => 'L', 'name' => 'Salles d\'auditions, de conférences, de réunions, de spectacles ou à usages multiples', 'isSpecial' => false],
            ['tag' => 'M', 'name' => 'Magasins de vente, centres commerciaux', 'isSpecial' => false],
            ['tag' => 'N', 'name' => 'Restaurants et débits de boissons', 'isSpecial' => false],
            ['tag' => 'O', 'name' => 'Hôtels et pensions de famille', 'isSpecial' => false],
            ['tag' => 'P', 'name' => 'Salles de danse et salles de jeux', 'isSpecial' => false],
            ['tag' => 'R', 'name' => 'Établissements d\'enseignement, colonies de vacances', 'isSpecial' => false],
            ['tag' => 'S', 'name' => 'Bibliothèques, centres de documentation', 'isSpecial' => false],
            ['tag' => 'T', 'name' => 'Salles d\'exposition', 'isSpecial' => false],
            ['tag' => 'U', 'name' => 'Établissements sanitaires', 'isSpecial' => false],
            ['tag' => 'V', 'name' => 'Établissements de culte', 'isSpecial' => false],
            ['tag' => 'W', 'name' => 'Administrations, banques, bureaux', 'isSpecial' => false],
            ['tag' => 'X', 'name' => 'Établissements sportifs couverts', 'isSpecial' => false],
            ['tag' => 'Y', 'name' => 'Musées', 'isSpecial' => false],
            ['tag' => 'EF', 'name' => 'Établissements flottants', 'isSpecial' => true],
            ['tag' => 'CTS', 'name' => 'Chapiteaux, tentes et structures', 'isSpecial' => true],
            ['tag' => 'SG', 'name' => 'Structures gonflables', 'isSpecial' => true],
            ['tag' => 'PS', 'name' => 'Parcs de stationnement couverts', 'isSpecial' => true],
            ['tag' => 'GA', 'name' => 'Gares accessibles au public', 'isSpecial' => true],
            ['tag' => 'OA', 'name' => 'Hôtels-restaurants d\'altitude', 'isSpecial' => true],
            ['tag' => 'REF', 'name' => 'Refuges de montagne', 'isSpecial' => true],
        ];

        foreach ($erpTypes as $type) {
            ErpType::create($type);
        }

        // Catégories ERP
        $erpCategories = [
            ['name' => 'Catégorie 1', 'groupe' => '1'],
            ['name' => 'Catégorie 2', 'groupe' => '2'],
            ['name' => 'Catégorie 3', 'groupe' => '3'],
            ['name' => 'Catégorie 4', 'groupe' => '4'],
            ['name' => 'Catégorie 5', 'groupe' => '5'],
        ];

        foreach ($erpCategories as $category) {
            ErpCategory::create($category);
        }

        // Classes IGH
        $ighClasses = [
            ['tag' => 'GHA', 'name' => 'Immeubles à usage d\'habitation'],
            ['tag' => 'GHO', 'name' => 'Immeubles à usage d\'hôtel'],
            ['tag' => 'GHR', 'name' => 'Immeubles à usage d\'enseignement'],
            ['tag' => 'GHS', 'name' => 'Immeubles à usage sanitaire'],
            ['tag' => 'GHU', 'name' => 'Immeubles à usage d\'habitation dont le plancher bas du logement le plus haut est situé à plus de 50 mètres du sol'],
            ['tag' => 'GHW', 'name' => 'Immeubles à usage de bureaux'],
            ['tag' => 'GHZ', 'name' => 'Immeubles à usage mixte'],
            ['tag' => 'ITGH', 'name' => 'Immeubles de très grande hauteur'],
        ];

        foreach ($ighClasses as $class) {
            IghClass::create($class);
        }

        // Familles HAB
        $habFamilles = [
            ['name' => 'Habitation 1ère famille'],
            ['name' => 'Habitation 2ème famille'],
            ['name' => 'Habitation 3ème famille'],
            ['name' => 'Habitation 4ème famille'],
        ];

        foreach ($habFamilles as $famille) {
            HabFamille::create($famille);
        }

        $this->command->info('Typologies créées avec succès !');
        $this->command->info('- ' . count($erpTypes) . ' types ERP créés');
        $this->command->info('- ' . count($erpCategories) . ' catégories ERP créées');
        $this->command->info('- ' . count($ighClasses) . ' classes IGH créées');
        $this->command->info('- ' . count($habFamilles) . ' familles HAB créées');
    }
}
