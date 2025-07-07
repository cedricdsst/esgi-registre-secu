<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\User;

class SitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les clients
        $clientHotel = User::where('email', 'pierre.bernard@hotel-grand-paris.com')->first();
        $clientCopropriete = User::where('email', 'marie.durand@syndic-paris.com')->first();
        $clientEntreprise = User::where('email', 'luc.moreau@techno-industries.com')->first();
        $clientEcole = User::where('email', 'anne.petit@lycee-voltaire.edu')->first();

        $sites = [
            [
                'nom' => 'Hôtel Grand Paris',
                'adresse' => '12 Avenue des Champs-Élysées',
                'code_postal' => '75008',
                'ville' => 'Paris',
                'pays' => 'France',
                'description' => 'Hôtel 5 étoiles au cœur de Paris avec 150 chambres, restaurant gastronomique, spa et centre de conférences.',
                'client_id' => $clientHotel->id,
            ],
            [
                'nom' => 'Résidence Les Jardins',
                'adresse' => '45 Rue de la République',
                'code_postal' => '69002',
                'ville' => 'Lyon',
                'pays' => 'France',
                'description' => 'Résidence moderne de 80 logements avec espaces verts, parking souterrain et local commercial.',
                'client_id' => $clientCopropriete->id,
            ],
            [
                'nom' => 'Complexe Techno Industries',
                'adresse' => 'Zone Industrielle du Parc',
                'code_postal' => '31700',
                'ville' => 'Blagnac',
                'pays' => 'France',
                'description' => 'Site industriel avec bureaux administratifs, ateliers de production, entrepôts et laboratoires de R&D.',
                'client_id' => $clientEntreprise->id,
            ],
            [
                'nom' => 'Lycée Voltaire',
                'adresse' => '101 Avenue Voltaire',
                'code_postal' => '75011',
                'ville' => 'Paris',
                'pays' => 'France',
                'description' => 'Établissement scolaire accueillant 1200 élèves avec salles de cours, laboratoires, gymnase et internat.',
                'client_id' => $clientEcole->id,
            ],
            [
                'nom' => 'Centre Commercial Rivoli',
                'adresse' => '234 Rue de Rivoli',
                'code_postal' => '75001',
                'ville' => 'Paris',
                'pays' => 'France',
                'description' => 'Centre commercial de 3 niveaux avec 120 boutiques, restaurants, cinéma et parking de 500 places.',
                'client_id' => $clientHotel->id,
            ],
            [
                'nom' => 'Résidence Étudiante Campus',
                'adresse' => '15 Boulevard du Campus',
                'code_postal' => '34000',
                'ville' => 'Montpellier',
                'pays' => 'France',
                'description' => 'Résidence étudiante de 200 logements avec espaces communs, salle de sport et laverie.',
                'client_id' => $clientCopropriete->id,
            ],
            [
                'nom' => 'Hôpital Saint-Michel',
                'adresse' => '88 Avenue de la Santé',
                'code_postal' => '13000',
                'ville' => 'Marseille',
                'pays' => 'France',
                'description' => 'Établissement hospitalier de 300 lits avec services d\'urgence, chirurgie, maternité et héliport.',
                'client_id' => $clientEntreprise->id,
            ],
            [
                'nom' => 'Médiathèque Centrale',
                'adresse' => '25 Place de la Culture',
                'code_postal' => '44000',
                'ville' => 'Nantes',
                'pays' => 'France',
                'description' => 'Médiathèque moderne sur 4 niveaux avec espaces de lecture, auditorium, ateliers et café littéraire.',
                'client_id' => $clientEcole->id,
            ],
        ];

        foreach ($sites as $siteData) {
            Site::create($siteData);
        }

        $this->command->info('Sites créés avec succès !');
        $this->command->info('- ' . count($sites) . ' sites créés avec des typologies variées');
    }
}
