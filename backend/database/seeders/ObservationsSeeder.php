<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Observation;
use App\Models\Rapport;
use App\Models\Intervention;
use App\Models\TypeIntervention;

class ObservationsSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les rapports de vérification réglementaire (seuls rapports pouvant avoir des observations)
        $typeVerificationReglementaire = TypeIntervention::where('nom', 'Vérification réglementaire')->first();
        
        $rapports = Rapport::whereHas('intervention', function ($query) use ($typeVerificationReglementaire) {
            $query->where('type_intervention_id', $typeVerificationReglementaire->id);
        })->get();

        $observations = [];

        foreach ($rapports as $rapport) {
            $intervention = $rapport->intervention;
            $partiesRapport = $rapport->parties;

            // Créer des observations selon le type d'intervention
            if (str_contains($intervention->intitule, 'sécurité incendie')) {
                $observationsData = [
                    [
                        'identification' => 'SSI-001',
                        'libelle' => 'Défaillance partielle du système d\'alarme au 2ème étage',
                        'localisation' => '2ème étage - Couloir principal',
                        'priorite' => 'urgent',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                    [
                        'identification' => 'SSI-002',
                        'libelle' => 'Extincteur manquant dans le local technique',
                        'localisation' => 'Sous-sol - Local technique',
                        'priorite' => 'normal',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            } elseif (str_contains($intervention->intitule, 'électriques')) {
                $observationsData = [
                    [
                        'identification' => 'ELEC-001',
                        'libelle' => 'Prise de courant défectueuse dans la cuisine',
                        'localisation' => 'Restaurant - Cuisine',
                        'priorite' => 'urgent',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                    [
                        'identification' => 'ELEC-002',
                        'libelle' => 'Éclairage de sécurité défaillant',
                        'localisation' => 'Couloir - Sortie de secours',
                        'priorite' => 'normal',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => true,
                        'date_signalement_precedent' => now()->subDays(90),
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            } elseif (str_contains($intervention->intitule, 'ascenseurs')) {
                $observationsData = [
                    [
                        'identification' => 'ASC-001',
                        'libelle' => 'Bouton d\'alarme ascenseur principal non fonctionnel',
                        'localisation' => 'Ascenseur principal - Cabine',
                        'priorite' => 'urgent',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            } elseif (str_contains($intervention->intitule, 'parties communes')) {
                $observationsData = [
                    [
                        'identification' => 'COM-001',
                        'libelle' => 'Porte coupe-feu ne se ferme pas correctement',
                        'localisation' => 'Hall d\'entrée - Accès parking',
                        'priorite' => 'normal',
                        'statut_traitement' => 'en_cours',
                        'deja_signalee' => true,
                        'date_signalement_precedent' => now()->subDays(45),
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                    [
                        'identification' => 'COM-002',
                        'libelle' => 'Détecteur de fumée encrassé',
                        'localisation' => 'Local vélos',
                        'priorite' => 'faible',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            } elseif (str_contains($intervention->intitule, 'équipements')) {
                $observationsData = [
                    [
                        'identification' => 'EQP-001',
                        'libelle' => 'Système de ventilation bruyant',
                        'localisation' => 'Salle de classe - Étage 1',
                        'priorite' => 'faible',
                        'statut_traitement' => 'traite',
                        'deja_signalee' => true,
                        'date_signalement_precedent' => now()->subDays(60),
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                    [
                        'identification' => 'EQP-002',
                        'libelle' => 'Thermostat défaillant laboratoire',
                        'localisation' => 'Laboratoire - Rez-de-chaussée',
                        'priorite' => 'normal',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            } else {
                // Observations génériques
                $observationsData = [
                    [
                        'identification' => 'GEN-001',
                        'libelle' => 'Observation générale de maintenance',
                        'localisation' => 'Zone principale',
                        'priorite' => 'normal',
                        'statut_traitement' => 'nouveau',
                        'deja_signalee' => false,
                        'date_signalement_precedent' => null,
                        'parties' => $partiesRapport->take(1)->pluck('id')->toArray(),
                    ],
                ];
            }

            // Ajouter les observations à la liste
            foreach ($observationsData as $observationData) {
                $observationData['rapport_id'] = $rapport->id;
                $observations[] = $observationData;
            }
        }

        // Créer les observations en base
        foreach ($observations as $observationData) {
            $parties = $observationData['parties'];
            unset($observationData['parties']);

            $observation = Observation::create($observationData);
            
            // Associer les parties à l'observation
            if (!empty($parties)) {
                $observation->parties()->attach($parties);
            }
        }

        $this->command->info('Observations créées avec succès !');
        $this->command->info('- ' . count($observations) . ' observations créées');
        $this->command->info('- ' . collect($observations)->where('priorite', 'urgent')->count() . ' observations urgentes');
        $this->command->info('- ' . collect($observations)->where('priorite', 'normal')->count() . ' observations normales');
        $this->command->info('- ' . collect($observations)->where('priorite', 'faible')->count() . ' observations faibles');
        $this->command->info('- ' . collect($observations)->where('statut_traitement', 'nouveau')->count() . ' observations nouvelles');
        $this->command->info('- ' . collect($observations)->where('deja_signalee', true)->count() . ' observations déjà signalées');
    }
} 