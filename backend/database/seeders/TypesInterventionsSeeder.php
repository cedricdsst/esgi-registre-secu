<?php

namespace Database\Seeders;

use App\Models\TypeIntervention;
use Illuminate\Database\Seeder;

class TypesInterventionsSeeder extends Seeder
{
    public function run()
    {
        $typesInterventions = [
            [
                'nom' => 'Vérification réglementaire',
                'ordre_priorite' => 1,
                'description' => 'Contrôle de conformité réglementaire des équipements et installations'
            ],
            [
                'nom' => 'Suivi d\'observation',
                'ordre_priorite' => 2,
                'description' => 'Suivi et traitement des observations identifiées lors de vérifications précédentes'
            ],
            [
                'nom' => 'Contrôle administratif',
                'ordre_priorite' => 3,
                'description' => 'Vérification administrative des documents et procédures'
            ]
        ];

        foreach ($typesInterventions as $typeIntervention) {
            TypeIntervention::create($typeIntervention);
        }
    }
}
