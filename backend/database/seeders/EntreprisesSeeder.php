<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entreprise;

class EntreprisesSeeder extends Seeder
{
    public function run()
    {
        $entreprises = [
            [
                'nom' => 'AXIGNIS SAS',
                'contact' => 'Jean Dupont',
                'telephone' => '01 23 45 67 89',
                'email' => 'contact@axignis.com',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'SÉCURITÉ CONTRÔLE',
                'contact' => 'Marie Lambert',
                'telephone' => '01 34 56 78 90',
                'email' => 'contact@securite-controle.fr',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'BUREAU VERITAS',
                'contact' => 'Pierre Martin',
                'telephone' => '01 45 67 89 01',
                'email' => 'info@bureauveritas.com',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'APAVE',
                'contact' => 'Sophie Dubois',
                'telephone' => '01 56 78 90 12',
                'email' => 'contact@apave.com',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'CSTB',
                'contact' => 'Luc Moreau',
                'telephone' => '01 67 89 01 23',
                'email' => 'info@cstb.fr',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'MAINTENANCE PLUS',
                'contact' => 'Anne Petit',
                'telephone' => '01 78 90 12 34',
                'email' => 'contact@maintenance-plus.fr',
                'is_organisme_agree' => false,
            ],
            [
                'nom' => 'TECHNI-SERVICES',
                'contact' => 'Marc Leroy',
                'telephone' => '01 89 01 23 45',
                'email' => 'info@techni-services.com',
                'is_organisme_agree' => false,
            ],
            [
                'nom' => 'EXPERT BÂTIMENT',
                'contact' => 'Claire Roux',
                'telephone' => '01 90 12 34 56',
                'email' => 'contact@expert-batiment.fr',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'CONTRÔLE TECHNIQUE PARIS',
                'contact' => 'David Simon',
                'telephone' => '01 01 23 45 67',
                'email' => 'info@ct-paris.fr',
                'is_organisme_agree' => true,
            ],
            [
                'nom' => 'SÉCURITÉ INCENDIE LYON',
                'contact' => 'Émilie Blanc',
                'telephone' => '04 78 90 12 34',
                'email' => 'contact@si-lyon.fr',
                'is_organisme_agree' => true,
            ],
        ];

        foreach ($entreprises as $entreprise) {
            Entreprise::create($entreprise);
        }

        $this->command->info('Entreprises créées avec succès !');
        $this->command->info('- ' . count($entreprises) . ' entreprises créées');
        $this->command->info('- ' . collect($entreprises)->where('is_organisme_agree', true)->count() . ' organismes agréés');
    }
} 