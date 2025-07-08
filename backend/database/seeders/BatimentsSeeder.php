<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\Batiment;
use App\Models\Niveau;
use App\Models\Partie;
use App\Models\Lot;
use App\Models\Erp;
use App\Models\Igh;
use App\Models\Hab;
use App\Models\Bup;
use App\Models\ErpType;
use App\Models\IghClass;
use App\Models\HabFamille;

class BatimentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::all();

        foreach ($sites as $site) {
            $this->createBatimentsForSite($site);
        }

        $this->command->info('Bâtiments, niveaux et parties créés avec succès !');
    }

    private function createBatimentsForSite(Site $site)
    {
        switch ($site->nom) {
            case 'Hôtel Grand Paris':
                $this->createHotelBatiments($site);
                break;
            case 'Résidence Les Jardins':
                $this->createResidenceBatiments($site);
                break;
            case 'Complexe Techno Industries':
                $this->createIndustrialBatiments($site);
                break;
            case 'Lycée Voltaire':
                $this->createSchoolBatiments($site);
                break;
            case 'Centre Commercial Rivoli':
                $this->createCommercialBatiments($site);
                break;
            case 'Résidence Étudiante Campus':
                $this->createStudentResidenceBatiments($site);
                break;
            case 'Hôpital Saint-Michel':
                $this->createHospitalBatiments($site);
                break;
            case 'Médiathèque Centrale':
                $this->createLibraryBatiments($site);
                break;
        }
    }

    private function createHotelBatiments(Site $site)
    {
        // Bâtiment principal - ERP type O (Hôtels)
        $batiment = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment Principal',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        // Créer l'ERP
        $erpType = ErpType::where('tag', 'O')->first();
        Erp::create([
            'name' => 'Hôtel Grand Paris',
            'batiment_id' => $batiment->id,
            'erp_categorie' => '2',
            'erp_type_id' => $erpType->id,
        ]);

        // Niveaux
        $niveaux = [
            ['nom' => 'Sous-sol', 'numero_etage' => -1, 'description' => 'Parking et locaux techniques'],
            ['nom' => 'Rez-de-chaussée', 'numero_etage' => 0, 'description' => 'Réception, restaurant, salon'],
            ['nom' => '1er étage', 'numero_etage' => 1, 'description' => 'Chambres et spa'],
            ['nom' => '2ème étage', 'numero_etage' => 2, 'description' => 'Chambres'],
            ['nom' => '3ème étage', 'numero_etage' => 3, 'description' => 'Chambres et suites'],
            ['nom' => '4ème étage', 'numero_etage' => 4, 'description' => 'Suites et centre de conférences'],
        ];

        foreach ($niveaux as $niveauData) {
            $niveau = Niveau::create([
                'batiment_id' => $batiment->id,
                'nom' => $niveauData['nom'],
                'numero_etage' => $niveauData['numero_etage'],
                'description' => $niveauData['description'],
            ]);

            $this->createPartiesForHotelNiveau($niveau);
        }
    }

    private function createPartiesForHotelNiveau(Niveau $niveau)
    {
        // Récupérer le bâtiment
        $batiment = $niveau->batiment;
        
        switch ($niveau->numero_etage) {
            case -1: // Sous-sol
                $parties = [
                    [
                        'nom' => 'Parking',
                        'type' => 'commune',
                        'activites_erp' => 'Stationnement véhicules',
                        'niveaux_data' => [
                            'effectif_public' => 100,
                            'personnel' => 2,
                            'surface_exploitation' => 800.0,
                            'surface_gla' => 800.0,
                            'surface_accessible_public' => 800.0,
                        ]
                    ],
                    [
                        'nom' => 'Local technique',
                        'type' => 'commune',
                        'activites_erp' => 'Techniques - chauffage, ventilation',
                        'niveaux_data' => [
                            'effectif_public' => 0,
                            'personnel' => 3,
                            'surface_exploitation' => 150.0,
                            'surface_gla' => 150.0,
                            'surface_accessible_public' => 0.0,
                        ]
                    ],
                    [
                        'nom' => 'Cave à vin',
                        'type' => 'commune',
                        'activites_erp' => 'Stockage boissons',
                        'niveaux_data' => [
                            'effectif_public' => 10,
                            'personnel' => 1,
                            'surface_exploitation' => 80.0,
                            'surface_gla' => 80.0,
                            'surface_accessible_public' => 80.0,
                        ]
                    ],
                ];
                break;
            case 0: // RDC
                $parties = [
                    [
                        'nom' => 'Réception',
                        'type' => 'commune',
                        'activites_erp' => 'Accueil, réception clientèle',
                        'niveaux_data' => [
                            'effectif_public' => 50,
                            'personnel' => 8,
                            'surface_exploitation' => 120.0,
                            'surface_gla' => 120.0,
                            'surface_accessible_public' => 120.0,
                        ]
                    ],
                    [
                        'nom' => 'Restaurant',
                        'type' => 'commune',
                        'activites_erp' => 'Restauration traditionnelle',
                        'niveaux_data' => [
                            'effectif_public' => 150,
                            'personnel' => 15,
                            'surface_exploitation' => 300.0,
                            'surface_gla' => 300.0,
                            'surface_accessible_public' => 250.0,
                        ]
                    ],
                    [
                        'nom' => 'Salon',
                        'type' => 'commune',
                        'activites_erp' => 'Détente, attente clientèle',
                        'niveaux_data' => [
                            'effectif_public' => 40,
                            'personnel' => 2,
                            'surface_exploitation' => 80.0,
                            'surface_gla' => 80.0,
                            'surface_accessible_public' => 80.0,
                        ]
                    ],
                    [
                        'nom' => 'Boutique',
                        'type' => 'commune',
                        'activites_erp' => 'Vente souvenirs, objets',
                        'niveaux_data' => [
                            'effectif_public' => 25,
                            'personnel' => 3,
                            'surface_exploitation' => 45.0,
                            'surface_gla' => 45.0,
                            'surface_accessible_public' => 45.0,
                        ]
                    ],
                ];
                break;
            default: // Étages
                $parties = [];
                for ($i = 1; $i <= 15; $i++) {
                    $parties[] = [
                        'nom' => "Chambre {$niveau->numero_etage}" . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'type' => 'privative',
                        'activites_erp' => 'Hébergement hôtelier',
                        'niveaux_data' => [
                            'effectif_public' => 4,
                            'personnel' => 0,
                            'surface_exploitation' => 25.0,
                            'surface_gla' => 25.0,
                            'surface_accessible_public' => 25.0,
                        ]
                    ];
                }
                $parties[] = [
                    'nom' => 'Couloir',
                    'type' => 'commune',
                    'activites_erp' => 'Circulation horizontale',
                    'niveaux_data' => [
                        'effectif_public' => 0,
                        'personnel' => 0,
                        'surface_exploitation' => 120.0,
                        'surface_gla' => 120.0,
                        'surface_accessible_public' => 120.0,
                    ]
                ];
                $parties[] = [
                    'nom' => 'Local ménage',
                    'type' => 'commune',
                    'activites_erp' => 'Entretien, nettoyage',
                    'niveaux_data' => [
                        'effectif_public' => 0,
                        'personnel' => 2,
                        'surface_exploitation' => 8.0,
                        'surface_gla' => 8.0,
                        'surface_accessible_public' => 0.0,
                    ]
                ];
                break;
        }

        foreach ($parties as $partieData) {
            $partie = Partie::create([
                'batiment_id' => $batiment->id,
                'nom' => $partieData['nom'],
                'type' => $partieData['type'],
                'isICPE' => false,
                'isPrivative' => $partieData['type'] === 'privative',
                'activites_erp' => $partieData['activites_erp'],
            ]);

            // Associer à ce niveau avec les données spécifiques
            $pivotData = array_merge([
                'libelle' => $partieData['nom'] . ' - ' . $niveau->nom,
            ], $partieData['niveaux_data']);
            
            $partie->niveaux()->attach($niveau->id, $pivotData);
        }
    }

    private function createResidenceBatiments(Site $site)
    {
        // Bâtiment A - HAB
        $batimentA = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment A',
            'type' => 'HAB',
            'isICPE' => false,
        ]);

        $habFamille = HabFamille::where('name', 'Habitation 3ème famille')->first();
        Hab::create([
            'name' => 'Résidence Les Jardins A',
            'batiment_id' => $batimentA->id,
            'hab_famille_id' => $habFamille->id,
        ]);

        // Bâtiment B - HAB
        $batimentB = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment B',
            'type' => 'HAB',
            'isICPE' => false,
        ]);

        Hab::create([
            'name' => 'Résidence Les Jardins B',
            'batiment_id' => $batimentB->id,
            'hab_famille_id' => $habFamille->id,
        ]);

        // Local commercial - ERP
        $localCommercial = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Local Commercial',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'M')->first();
        Erp::create([
            'name' => 'Commerces Les Jardins',
            'batiment_id' => $localCommercial->id,
            'erp_categorie' => '5',
            'erp_type_id' => $erpType->id,
        ]);

        $this->createNiveauxForResidence($batimentA, 'A');
        $this->createNiveauxForResidence($batimentB, 'B');
        $this->createNiveauxForCommerce($localCommercial);
    }

    private function createNiveauxForResidence(Batiment $batiment, string $letter)
    {
        for ($etage = 0; $etage <= 6; $etage++) {
            $niveau = Niveau::create([
                'batiment_id' => $batiment->id,
                'nom' => $etage == 0 ? 'Rez-de-chaussée' : "{$etage}ème étage",
                'numero_etage' => $etage,
                'description' => $etage == 0 ? 'Hall d\'entrée et locaux communs' : 'Logements',
            ]);

            if ($etage == 0) {
                // RDC : parties communes
                $parties = [
                    ['nom' => 'Hall d\'entrée', 'type' => 'commune'],
                    ['nom' => 'Local vélos', 'type' => 'commune'],
                    ['nom' => 'Local poubelles', 'type' => 'commune'],
                    ['nom' => 'Loge gardien', 'type' => 'commune'],
                ];
            } else {
                // Étages : logements HAB (pas de parties privatives selon consignes)
                $parties = [
                    ['nom' => "Logements étage {$etage}", 'type' => 'commune'],
                    ['nom' => 'Palier', 'type' => 'commune'],
                    ['nom' => 'Gaine technique', 'type' => 'commune'],
                ];
            }

            foreach ($parties as $partieData) {
                $partie = Partie::create([
                    'batiment_id' => $batiment->id,
                    'nom' => $partieData['nom'],
                    'type' => $partieData['type'],
                    'isICPE' => false,
                    'isPrivative' => $partieData['type'] === 'privative',
                    'activites_erp' => $partieData['type'] === 'privative' ? 'Logement résidentiel' : 'Parties communes résidentielles',
                ]);

                // Associer au niveau avec des données réalistes
                $pivotData = [
                    'libelle' => $partieData['nom'] . ' - ' . $niveau->nom,
                    'effectif_public' => ($partieData['nom'] === 'Loge gardien') ? 1 : 
                        (strpos($partieData['nom'], 'Logements') !== false ? 24 : 0), // 4 logements x 6 appts
                    'personnel' => ($partieData['nom'] === 'Loge gardien') ? 1 : 0,
                    'surface_exploitation' => ($partieData['nom'] === 'Hall d\'entrée') ? 100.0 : 
                        (strpos($partieData['nom'], 'Logements') !== false ? 450.0 : 20.0), // 75m² x 6 appts
                    'surface_gla' => ($partieData['nom'] === 'Hall d\'entrée') ? 100.0 : 
                        (strpos($partieData['nom'], 'Logements') !== false ? 450.0 : 20.0),
                    'surface_accessible_public' => ($partieData['nom'] === 'Hall d\'entrée') ? 100.0 : 
                        (strpos($partieData['nom'], 'Logements') !== false ? 450.0 : 20.0),
                ];
                
                $partie->niveaux()->attach($niveau->id, $pivotData);

                // Créer des lots pour les logements HAB (parties communes contenant les logements)
                if (strpos($partieData['nom'], 'Logements') !== false) {
                    // Créer 6 lots individuels pour les logements de cet étage
                    for ($appt = 1; $appt <= 6; $appt++) {
                        $lot = Lot::create([
                            'nom' => "Lot Appartement {$letter}{$etage}" . str_pad($appt, 2, '0', STR_PAD_LEFT),
                            'niveau' => $etage,
                            'type' => 'Logement',
                        ]);
                        $partie->lots()->attach($lot->id, [
                            'libelle' => "Logement {$letter}{$etage}" . str_pad($appt, 2, '0', STR_PAD_LEFT),
                            'type' => 'Habitation',
                        ]);
                    }
                }
            }
        }
    }

    private function createNiveauxForCommerce(Batiment $batiment)
    {
        $niveau = Niveau::create([
            'batiment_id' => $batiment->id,
            'nom' => 'Rez-de-chaussée',
            'numero_etage' => 0,
            'description' => 'Commerces de proximité',
        ]);

        $parties = [
            ['nom' => 'Boulangerie', 'type' => 'privative'],
            ['nom' => 'Pharmacie', 'type' => 'privative'],
            ['nom' => 'Superette', 'type' => 'privative'],
            ['nom' => 'Hall commun', 'type' => 'commune'],
            ['nom' => 'Local technique', 'type' => 'commune'],
        ];

        foreach ($parties as $partieData) {
            $partie = Partie::create([
                'batiment_id' => $batiment->id,
                'nom' => $partieData['nom'],
                'type' => $partieData['type'],
                'isICPE' => false,
                'isPrivative' => $partieData['type'] === 'privative',
                'activites_erp' => $partieData['type'] === 'privative' ? 'Commerce de détail' : 'Espaces communs commerciaux',
            ]);

            // Associer au niveau avec des données réalistes
            $pivotData = [
                'libelle' => $partieData['nom'] . ' - ' . $niveau->nom,
                'effectif_public' => $partieData['type'] === 'privative' ? 
                    ($partieData['nom'] === 'Superette' ? 50 : 25) : 10,
                'personnel' => $partieData['type'] === 'privative' ? 
                    ($partieData['nom'] === 'Superette' ? 8 : 3) : 1,
                'surface_exploitation' => $partieData['type'] === 'privative' ? 
                    ($partieData['nom'] === 'Superette' ? 200.0 : 80.0) : 40.0,
                'surface_gla' => $partieData['type'] === 'privative' ? 
                    ($partieData['nom'] === 'Superette' ? 200.0 : 80.0) : 40.0,
                'surface_accessible_public' => $partieData['type'] === 'privative' ? 
                    ($partieData['nom'] === 'Superette' ? 180.0 : 70.0) : 40.0,
            ];
            
            $partie->niveaux()->attach($niveau->id, $pivotData);

            // Créer des lots pour les commerces
            if ($partieData['type'] === 'privative') {
                $lot = Lot::create([
                    'nom' => "Lot " . $partieData['nom'],
                    'niveau' => 0,
                    'type' => 'Commerce',
                ]);
                $partie->lots()->attach($lot->id, [
                    'libelle' => 'Commerce principal',
                    'type' => 'Commerce',
                ]);
            }
        }
    }

    private function createIndustrialBatiments(Site $site)
    {
        // Bâtiment administratif - ERP type W
        $admin = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment Administratif',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'W')->first();
        Erp::create([
            'name' => 'Bureaux Techno Industries',
            'batiment_id' => $admin->id,
            'erp_categorie' => '3',
            'erp_type_id' => $erpType->id,
        ]);

        // Atelier de production - ICPE
        $atelier = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Atelier de Production',
            'type' => 'ICPE',
            'isICPE' => true,
        ]);

        // Entrepôt - BUP
        $entrepot = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Entrepôt',
            'type' => 'BUP',
            'isICPE' => false,
        ]);

        Bup::create([
            'name' => 'Entrepôt Techno',
            'batiment_id' => $entrepot->id,
        ]);

        $this->createSimpleNiveaux($admin, ['Rez-de-chaussée', '1er étage', '2ème étage']);
        $this->createSimpleNiveaux($atelier, ['Rez-de-chaussée']);
        $this->createSimpleNiveaux($entrepot, ['Rez-de-chaussée']);
    }

    private function createSchoolBatiments(Site $site)
    {
        // Bâtiment principal - ERP type R
        $principal = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment Principal',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'R')->first();
        Erp::create([
            'name' => 'Lycée Voltaire',
            'batiment_id' => $principal->id,
            'erp_categorie' => '1',
            'erp_type_id' => $erpType->id,
        ]);

        // Gymnase - ERP type X
        $gymnase = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Gymnase',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpTypeX = ErpType::where('tag', 'X')->first();
        Erp::create([
            'name' => 'Gymnase Voltaire',
            'batiment_id' => $gymnase->id,
            'erp_categorie' => '3',
            'erp_type_id' => $erpTypeX->id,
        ]);

        // Internat - HAB
        $internat = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Internat',
            'type' => 'HAB',
            'isICPE' => false,
        ]);

        $habFamille = HabFamille::where('name', 'Habitation 2ème famille')->first();
        Hab::create([
            'name' => 'Internat Voltaire',
            'batiment_id' => $internat->id,
            'hab_famille_id' => $habFamille->id,
        ]);

        $this->createSimpleNiveaux($principal, ['Rez-de-chaussée', '1er étage', '2ème étage', '3ème étage']);
        $this->createSimpleNiveaux($gymnase, ['Rez-de-chaussée']);
        $this->createSimpleNiveaux($internat, ['Rez-de-chaussée', '1er étage', '2ème étage']);
    }

    private function createCommercialBatiments(Site $site)
    {
        // Centre commercial - ERP type M
        $centre = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Centre Commercial',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'M')->first();
        Erp::create([
            'name' => 'Centre Commercial Rivoli',
            'batiment_id' => $centre->id,
            'erp_categorie' => '1',
            'erp_type_id' => $erpType->id,
        ]);

        // Parking - ERP type PS
        $parking = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Parking',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpTypePS = ErpType::where('tag', 'PS')->first();
        Erp::create([
            'name' => 'Parking Rivoli',
            'batiment_id' => $parking->id,
            'erp_categorie' => '2',
            'erp_type_id' => $erpTypePS->id,
        ]);

        $this->createSimpleNiveaux($centre, ['Rez-de-chaussée', '1er étage', '2ème étage']);
        $this->createSimpleNiveaux($parking, ['Sous-sol -1', 'Sous-sol -2']);
    }

    private function createStudentResidenceBatiments(Site $site)
    {
        // Résidence - HAB
        $residence = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Résidence Étudiante',
            'type' => 'HAB',
            'isICPE' => false,
        ]);

        $habFamille = HabFamille::where('name', 'Habitation 3ème famille')->first();
        Hab::create([
            'name' => 'Campus Montpellier',
            'batiment_id' => $residence->id,
            'hab_famille_id' => $habFamille->id,
        ]);

        $this->createSimpleNiveaux($residence, ['Rez-de-chaussée', '1er étage', '2ème étage', '3ème étage', '4ème étage']);
    }

    private function createHospitalBatiments(Site $site)
    {
        // Hôpital - ERP type U
        $hopital = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Hôpital Principal',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'U')->first();
        Erp::create([
            'name' => 'Hôpital Saint-Michel',
            'batiment_id' => $hopital->id,
            'erp_categorie' => '1',
            'erp_type_id' => $erpType->id,
        ]);

        $this->createSimpleNiveaux($hopital, ['Sous-sol', 'Rez-de-chaussée', '1er étage', '2ème étage', '3ème étage']);
    }

    private function createLibraryBatiments(Site $site)
    {
        // Médiathèque - ERP type S
        $mediatheque = Batiment::create([
            'site_id' => $site->id,
            'name' => 'Médiathèque',
            'type' => 'ERP',
            'isICPE' => false,
        ]);

        $erpType = ErpType::where('tag', 'S')->first();
        Erp::create([
            'name' => 'Médiathèque Centrale Nantes',
            'batiment_id' => $mediatheque->id,
            'erp_categorie' => '2',
            'erp_type_id' => $erpType->id,
        ]);

        $this->createSimpleNiveaux($mediatheque, ['Rez-de-chaussée', '1er étage', '2ème étage', '3ème étage']);
    }

    private function createSimpleNiveaux(Batiment $batiment, array $niveauxNames)
    {
        foreach ($niveauxNames as $index => $nom) {
            $numeroEtage = $this->getNumeroEtage($nom, $index);
            
            $niveau = Niveau::create([
                'batiment_id' => $batiment->id,
                'nom' => $nom,
                'numero_etage' => $numeroEtage,
                'description' => "Niveau {$nom}",
            ]);

            // Créer quelques parties génériques
            $parties = [
                ['nom' => 'Zone principale', 'type' => 'commune'],
                ['nom' => 'Circulation', 'type' => 'commune'],
                ['nom' => 'Local technique', 'type' => 'commune'],
            ];

            foreach ($parties as $partieData) {
                $partie = Partie::create([
                    'batiment_id' => $batiment->id,
                    'nom' => $partieData['nom'],
                    'type' => $partieData['type'],
                    'isICPE' => false,
                    'isPrivative' => false,
                    'activites_erp' => $partieData['nom'] === 'Zone principale' ? 'Activité principale' : 'Espaces techniques/circulation',
                ]);

                // Associer au niveau avec des données génériques
                $pivotData = [
                    'libelle' => $partieData['nom'] . ' - ' . $niveau->nom,
                    'effectif_public' => $partieData['nom'] === 'Zone principale' ? 50 : 0,
                    'personnel' => $partieData['nom'] === 'Local technique' ? 2 : 0,
                    'surface_exploitation' => $partieData['nom'] === 'Zone principale' ? 200.0 : 50.0,
                    'surface_gla' => $partieData['nom'] === 'Zone principale' ? 200.0 : 50.0,
                    'surface_accessible_public' => $partieData['nom'] === 'Zone principale' ? 180.0 : 
                        ($partieData['nom'] === 'Circulation' ? 50.0 : 0.0),
                ];
                
                $partie->niveaux()->attach($niveau->id, $pivotData);

                // Créer des lots pour les parties génériques
                if ($partieData['type'] === 'commune') {
                    $lot = Lot::create([
                        'nom' => "Lot " . $partieData['nom'],
                        'niveau' => $numeroEtage,
                        'type' => 'Zone',
                    ]);
                    $partie->lots()->attach($lot->id, [
                        'libelle' => $partieData['nom'] . ' - ' . $niveau->nom,
                        'type' => 'Zone',
                    ]);
                }
            }
        }
    }

    private function getNumeroEtage(string $nom, int $index): int
    {
        if (str_contains($nom, 'Sous-sol')) {
            return -1 - $index;
        }
        if (str_contains($nom, 'Rez-de-chaussée')) {
            return 0;
        }
        if (preg_match('/(\d+)/', $nom, $matches)) {
            return (int) $matches[1];
        }
        return $index;
    }
}
