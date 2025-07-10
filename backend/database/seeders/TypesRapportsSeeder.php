<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeRapport;

class TypesRapportsSeeder extends Seeder
{
    public function run()
    {
        $typesRapports = [
            // Types ERP
            [
                'libelle' => 'Vérification périodique des installations électriques',
                'sous_titre' => 'Conformité aux normes NF C 15-100',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'ERP',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Contrôle des systèmes de sécurité incendie',
                'sous_titre' => 'SSI - Système de Sécurité Incendie',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'ERP',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Vérification des moyens d\'évacuation',
                'sous_titre' => 'Désenfumage et éclairage de sécurité',
                'periodicite' => 'semestrielle',
                'typologie_batiment' => 'ERP',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Contrôle des ascenseurs',
                'sous_titre' => 'Vérification technique approfondie',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'ERP',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Vérification des installations de chauffage',
                'sous_titre' => 'Chaudières et circuits de distribution',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'ERP',
                'organisme_agree_requis' => false,
            ],
            
            // Types IGH
            [
                'libelle' => 'Contrôle technique IGH',
                'sous_titre' => 'Vérification réglementaire périodique',
                'periodicite' => 'triennale',
                'typologie_batiment' => 'IGH',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Vérification des colonnes sèches',
                'sous_titre' => 'Équipements de lutte contre l\'incendie',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'IGH',
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Contrôle des systèmes de pressurisation',
                'sous_titre' => 'Escaliers et ascenseurs',
                'periodicite' => 'semestrielle',
                'typologie_batiment' => 'IGH',
                'organisme_agree_requis' => true,
            ],
            
            // Types HAB
            [
                'libelle' => 'Vérification des installations communes',
                'sous_titre' => 'Électricité et gaz des parties communes',
                'periodicite' => 'quinquennale',
                'typologie_batiment' => 'HAB',
                'organisme_agree_requis' => false,
            ],
            [
                'libelle' => 'Contrôle des équipements de sécurité',
                'sous_titre' => 'Portes coupe-feu et éclairage de sécurité',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'HAB',
                'organisme_agree_requis' => false,
            ],
            [
                'libelle' => 'Vérification des VMC',
                'sous_titre' => 'Ventilation mécanique contrôlée',
                'periodicite' => 'biannuelle',
                'typologie_batiment' => 'HAB',
                'organisme_agree_requis' => false,
            ],
            
            // Types BUP
            [
                'libelle' => 'Contrôle des installations techniques',
                'sous_titre' => 'Vérification générale des équipements',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'BUP',
                'organisme_agree_requis' => false,
            ],
            [
                'libelle' => 'Vérification de la sécurité incendie',
                'sous_titre' => 'Moyens d\'extinction et d\'évacuation',
                'periodicite' => 'annuelle',
                'typologie_batiment' => 'BUP',
                'organisme_agree_requis' => false,
            ],
            
            // Types ponctuels
            [
                'libelle' => 'Expertise suite à sinistre',
                'sous_titre' => 'Évaluation des dommages et remise en état',
                'periodicite' => 'ponctuelle',
                'typologie_batiment' => null,
                'organisme_agree_requis' => true,
            ],
            [
                'libelle' => 'Diagnostic avant travaux',
                'sous_titre' => 'Évaluation préalable aux modifications',
                'periodicite' => 'ponctuelle',
                'typologie_batiment' => null,
                'organisme_agree_requis' => false,
            ],
            [
                'libelle' => 'Contrôle de réception',
                'sous_titre' => 'Vérification après travaux ou installation',
                'periodicite' => 'ponctuelle',
                'typologie_batiment' => null,
                'organisme_agree_requis' => false,
            ],
        ];

        foreach ($typesRapports as $typeRapport) {
            TypeRapport::create($typeRapport);
        }

        $this->command->info('Types de rapports créés avec succès !');
        $this->command->info('- ' . count($typesRapports) . ' types de rapports créés');
        $this->command->info('- ' . collect($typesRapports)->where('organisme_agree_requis', true)->count() . ' types nécessitant un organisme agréé');
    }
} 