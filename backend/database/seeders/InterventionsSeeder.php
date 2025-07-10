<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intervention;
use App\Models\TypeIntervention;
use App\Models\Partie;
use App\Models\Entreprise;

class InterventionsSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les types d'intervention
        $typeVerificationReglementaire = TypeIntervention::where('nom', 'Vérification réglementaire')->first();
        $typeSuiviObservation = TypeIntervention::where('nom', 'Suivi d\'observation')->first();
        $typeControleAdministratif = TypeIntervention::where('nom', 'Contrôle administratif')->first();

        // Récupérer quelques parties pour les associer aux interventions
        $partiesHotel = Partie::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Hôtel Grand Paris');
        })->take(5)->get();

        $partiesResidence = Partie::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Résidence Les Jardins');
        })->take(3)->get();

        $partiesLycee = Partie::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Lycée Voltaire');
        })->take(4)->get();

        $partiesHopital = Partie::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Hôpital Saint-Michel');
        })->take(3)->get();

        // Récupérer quelques entreprises
        $entreprises = Entreprise::take(6)->get();

        $interventions = [
            // Interventions à l'hôtel
            [
                'intitule' => 'Vérification annuelle des systèmes de sécurité incendie',
                'entreprise_nom' => 'AXIGNIS SAS',
                'intervenant_nom' => 'Jean Dupont',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'termine',
                'signed_at' => now()->subDays(10),
                'signed_by' => 'Pierre Bernard',
                'parties' => $partiesHotel->take(2)->pluck('id')->toArray(),
            ],
            [
                'intitule' => 'Contrôle des installations électriques - Restaurant',
                'entreprise_nom' => 'BUREAU VERITAS',
                'intervenant_nom' => 'Pierre Martin',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'en_cours',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesHotel->where('nom', 'Restaurant')->pluck('id')->toArray(),
            ],
            [
                'intitule' => 'Vérification des ascenseurs',
                'entreprise_nom' => 'APAVE',
                'intervenant_nom' => 'Sophie Dubois',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'planifie',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesHotel->take(1)->pluck('id')->toArray(),
            ],

            // Interventions à la résidence
            [
                'intitule' => 'Contrôle des parties communes',
                'entreprise_nom' => 'SÉCURITÉ CONTRÔLE',
                'intervenant_nom' => 'Marie Lambert',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'termine',
                'signed_at' => now()->subDays(20),
                'signed_by' => 'Marie Durand',
                'parties' => $partiesResidence->pluck('id')->toArray(),
            ],
            [
                'intitule' => 'Suivi observations précédentes - Hall d\'entrée',
                'entreprise_nom' => 'MAINTENANCE PLUS',
                'intervenant_nom' => 'Anne Petit',
                'type_intervention_id' => $typeSuiviObservation->id,
                'statut' => 'en_cours',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesResidence->take(1)->pluck('id')->toArray(),
            ],

            // Interventions au lycée
            [
                'intitule' => 'Vérification périodique des équipements de sécurité',
                'entreprise_nom' => 'EXPERT BÂTIMENT',
                'intervenant_nom' => 'Claire Roux',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'termine',
                'signed_at' => now()->subDays(5),
                'signed_by' => 'Anne Petit',
                'parties' => $partiesLycee->take(3)->pluck('id')->toArray(),
            ],
            [
                'intitule' => 'Contrôle administratif des registres de sécurité',
                'entreprise_nom' => 'CONTRÔLE TECHNIQUE PARIS',
                'intervenant_nom' => 'David Simon',
                'type_intervention_id' => $typeControleAdministratif->id,
                'statut' => 'planifie',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesLycee->take(1)->pluck('id')->toArray(),
            ],

            // Interventions à l'hôpital
            [
                'intitule' => 'Vérification des équipements médicaux de sécurité',
                'entreprise_nom' => 'CSTB',
                'intervenant_nom' => 'Luc Moreau',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'en_cours',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesHopital->pluck('id')->toArray(),
            ],
            [
                'intitule' => 'Contrôle des installations électriques - Bloc opératoire',
                'entreprise_nom' => 'SÉCURITÉ INCENDIE LYON',
                'intervenant_nom' => 'Émilie Blanc',
                'type_intervention_id' => $typeVerificationReglementaire->id,
                'statut' => 'planifie',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesHopital->take(1)->pluck('id')->toArray(),
            ],

            // Interventions de suivi
            [
                'intitule' => 'Suivi des observations - Système de ventilation',
                'entreprise_nom' => 'TECHNI-SERVICES',
                'intervenant_nom' => 'Marc Leroy',
                'type_intervention_id' => $typeSuiviObservation->id,
                'statut' => 'planifie',
                'signed_at' => null,
                'signed_by' => null,
                'parties' => $partiesHotel->take(1)->pluck('id')->toArray(),
            ],
        ];

        foreach ($interventions as $interventionData) {
            $parties = $interventionData['parties'];
            unset($interventionData['parties']);

            $intervention = Intervention::create($interventionData);
            
            // Associer les parties à l'intervention
            if (!empty($parties)) {
                $intervention->parties()->attach($parties);
            }
        }

        $this->command->info('Interventions créées avec succès !');
        $this->command->info('- ' . count($interventions) . ' interventions créées');
        $this->command->info('- ' . collect($interventions)->where('statut', 'termine')->count() . ' interventions terminées');
        $this->command->info('- ' . collect($interventions)->where('statut', 'en_cours')->count() . ' interventions en cours');
        $this->command->info('- ' . collect($interventions)->where('statut', 'planifie')->count() . ' interventions planifiées');
    }
} 