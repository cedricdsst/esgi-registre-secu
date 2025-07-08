<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Site;
use App\Models\Batiment;
use App\Models\Niveau;
use App\Models\Partie;
use App\Models\DroitsSite;
use App\Models\DroitsBatiment;
use App\Models\DroitsNiveau;
use App\Models\DroitsPartie;

class DroitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs
        $userAxignis = User::where('email', 'sophie.martin@axignis.com')->first();
        $clientHotel = User::where('email', 'pierre.bernard@hotel-grand-paris.com')->first();
        $clientCopropriete = User::where('email', 'marie.durand@syndic-paris.com')->first();

        // Donner des droits croisés pour tester les permissions granulaires
        
        // L'utilisateur AXIGNIS a accès en lecture à tous les sites de l'hôtel
        $sitesHotel = Site::where('client_id', $clientHotel->id)->get();
        foreach ($sitesHotel as $site) {
            DroitsSite::create([
                'utilisateur_id' => $userAxignis->id,
                'site_id' => $site->id,
                'lecture' => true,
                'ecriture' => false,
            ]);
        }

        // Le client copropriété a accès en lecture/écriture au lycée (partenariat)
        $lycee = Site::where('nom', 'Lycée Voltaire')->first();
        if ($lycee) {
            DroitsSite::create([
                'utilisateur_id' => $clientCopropriete->id,
                'site_id' => $lycee->id,
                'lecture' => true,
                'ecriture' => true,
            ]);
        }

        // Droits spécifiques sur des bâtiments
        $hotelBatiment = Batiment::whereHas('site', function ($query) {
            $query->where('nom', 'Hôtel Grand Paris');
        })->first();

        if ($hotelBatiment) {
            // L'utilisateur AXIGNIS a des droits d'écriture sur le bâtiment principal de l'hôtel
            DroitsBatiment::create([
                'utilisateur_id' => $userAxignis->id,
                'batiment_id' => $hotelBatiment->id,
                'lecture' => true,
                'ecriture' => true,
            ]);
        }

        // Droits sur des niveaux spécifiques
        $niveauHotel = Niveau::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Hôtel Grand Paris');
        })->where('numero_etage', 4)->first();

        if ($niveauHotel) {
            // Le client copropriété a accès au 4ème étage (suites et centre de conférences)
            DroitsNiveau::create([
                'utilisateur_id' => $clientCopropriete->id,
                'niveau_id' => $niveauHotel->id,
                'lecture' => true,
                'ecriture' => false,
            ]);
        }

        // Droits sur des parties spécifiques
        $partieRestaurant = Partie::whereHas('batiment.site', function ($query) {
            $query->where('nom', 'Hôtel Grand Paris');
        })->where('nom', 'Restaurant')->first();

        if ($partieRestaurant) {
            // L'utilisateur AXIGNIS a des droits complets sur le restaurant
            DroitsPartie::create([
                'utilisateur_id' => $userAxignis->id,
                'partie_id' => $partieRestaurant->id,
                'lecture' => true,
                'ecriture' => true,
            ]);
        }

        // Droits sur la résidence étudiante
        $residenceEtudiante = Site::where('nom', 'Résidence Étudiante Campus')->first();
        if ($residenceEtudiante) {
            // L'utilisateur AXIGNIS a accès en lecture
            DroitsSite::create([
                'utilisateur_id' => $userAxignis->id,
                'site_id' => $residenceEtudiante->id,
                'lecture' => true,
                'ecriture' => false,
            ]);
        }

        $this->command->info('Droits d\'accès créés avec succès !');
        $this->command->info('- Droits croisés entre utilisateurs AXIGNIS et clients');
        $this->command->info('- Permissions granulaires sur sites, bâtiments, niveaux et parties');
        $this->command->info('- Scénarios de test pour la gestion des droits');
    }
} 