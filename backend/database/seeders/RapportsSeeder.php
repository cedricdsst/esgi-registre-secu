<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rapport;
use App\Models\Intervention;
use App\Models\TypeRapport;

class RapportsSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les interventions terminées pour créer des rapports
        $interventionsTerminees = Intervention::where('statut', 'termine')->get();
        
        // Récupérer quelques interventions en cours pour créer des rapports brouillons
        $interventionsEnCours = Intervention::where('statut', 'en_cours')->take(2)->get();

        // Récupérer les types de rapports
        $typeElectrique = TypeRapport::where('libelle', 'Vérification périodique des installations électriques')->first();
        $typeIncendie = TypeRapport::where('libelle', 'Contrôle des systèmes de sécurité incendie')->first();
        $typeAscenseur = TypeRapport::where('libelle', 'Contrôle des ascenseurs')->first();
        $typeSecutiteHab = TypeRapport::where('libelle', 'Contrôle des équipements de sécurité')->first();
        $typeInstallationsTechniques = TypeRapport::where('libelle', 'Contrôle des installations techniques')->first();
        $typeEvacuation = TypeRapport::where('libelle', 'Vérification des moyens d\'évacuation')->first();

        $rapports = [];

        // Créer des rapports pour les interventions terminées
        foreach ($interventionsTerminees as $intervention) {
            $typeRapport = null;
            $equipements = [];

            // Associer un type de rapport selon l'intitulé de l'intervention
            if (str_contains($intervention->intitule, 'sécurité incendie')) {
                $typeRapport = $typeIncendie;
                $equipements = [101, 102, 103]; // IDs fictifs d'équipements incendie
            } elseif (str_contains($intervention->intitule, 'électriques')) {
                $typeRapport = $typeElectrique;
                $equipements = [201, 202, 203, 204]; // IDs fictifs d'équipements électriques
            } elseif (str_contains($intervention->intitule, 'ascenseurs')) {
                $typeRapport = $typeAscenseur;
                $equipements = [301, 302]; // IDs fictifs d'ascenseurs
            } elseif (str_contains($intervention->intitule, 'parties communes')) {
                $typeRapport = $typeSecutiteHab;
                $equipements = [401, 402, 403]; // IDs fictifs d'équipements de sécurité HAB
            } elseif (str_contains($intervention->intitule, 'équipements')) {
                $typeRapport = $typeInstallationsTechniques;
                $equipements = [501, 502, 503, 504]; // IDs fictifs d'équipements techniques
            } else {
                // Type par défaut
                $typeRapport = $typeEvacuation;
                $equipements = [601, 602]; // IDs fictifs d'équipements d'évacuation
            }

            if ($typeRapport) {
                $rapport = [
                    'intervention_id' => $intervention->id,
                    'type_rapport_id' => $typeRapport->id,
                    'date_emission' => now()->subDays(rand(1, 30)),
                    'statut' => $intervention->signed_at ? 'signe' : 'finalise',
                    'equipements_selection' => json_encode($equipements),
                    'parties' => $intervention->parties->pluck('id')->toArray(),
                ];
                $rapports[] = $rapport;
            }
        }

        // Créer des rapports brouillons pour les interventions en cours
        foreach ($interventionsEnCours as $intervention) {
            $typeRapport = $typeElectrique; // Type par défaut pour les brouillons
            $equipements = [201, 202]; // Équipements partiels

            $rapport = [
                'intervention_id' => $intervention->id,
                'type_rapport_id' => $typeRapport->id,
                'date_emission' => now(),
                'statut' => 'brouillon',
                'equipements_selection' => json_encode($equipements),
                'parties' => $intervention->parties->take(1)->pluck('id')->toArray(),
            ];
            $rapports[] = $rapport;
        }

        // Créer les rapports en base
        foreach ($rapports as $rapportData) {
            $parties = $rapportData['parties'];
            unset($rapportData['parties']);

            $rapport = Rapport::create($rapportData);
            
            // Associer les parties au rapport
            if (!empty($parties)) {
                $rapport->parties()->attach($parties);
            }
        }

        $this->command->info('Rapports créés avec succès !');
        $this->command->info('- ' . count($rapports) . ' rapports créés');
        $this->command->info('- ' . collect($rapports)->where('statut', 'signe')->count() . ' rapports signés');
        $this->command->info('- ' . collect($rapports)->where('statut', 'finalise')->count() . ' rapports finalisés');
        $this->command->info('- ' . collect($rapports)->where('statut', 'brouillon')->count() . ' rapports brouillons');
    }
} 