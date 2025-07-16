import type { User, Site, Building, Partie } from '../types';

/**
 * Vérifie si un utilisateur entreprise peut voir un site
 * (le site doit contenir des bâtiments avec des parties dont l'utilisateur est propriétaire)
 */
export const canAccessSite = (site: Site, user: User): boolean => {
    if (!site.batiments || !user) return false;
    
    return site.batiments.some(batiment => {
        if (!batiment.parties) return false;
        return batiment.parties.some(partie => partie.owner_id === user.id);
    });
};

/**
 * Vérifie si un utilisateur entreprise peut voir un bâtiment
 * (le bâtiment doit contenir des parties dont l'utilisateur est propriétaire)
 */
export const canAccessBuilding = (building: Building, user: User): boolean => {
    if (!building.parties || !user) return false;
    return building.parties.some(partie => partie.owner_id === user.id);
};

/**
 * Vérifie si un utilisateur entreprise peut voir une partie
 * (l'utilisateur doit être le propriétaire de la partie)
 */
export const canAccessPartie = (partie: Partie, user: User): boolean => {
    if (!user) return false;
    return partie.owner_id === user.id;
};

/**
 * Filtre une liste de sites selon les permissions de l'utilisateur
 */
export const filterSitesForUser = (sites: Site[], user: User, role: string): Site[] => {
    if (role === 'user-entreprise') {
        return sites.filter(site => canAccessSite(site, user));
    }
    // Pour les autres rôles, retourner tous les sites
    return sites;
};

/**
 * Filtre une liste de bâtiments selon les permissions de l'utilisateur
 */
export const filterBuildingsForUser = (buildings: Building[], user: User, role: string): Building[] => {
    if (role === 'user-entreprise') {
        return buildings.filter(building => canAccessBuilding(building, user));
    }
    // Pour les autres rôles, retourner tous les bâtiments
    return buildings;
};

/**
 * Filtre une liste de parties selon les permissions de l'utilisateur
 */
export const filterPartiesForUser = (parties: Partie[], user: User, role: string): Partie[] => {
    if (role === 'user-entreprise') {
        return parties.filter(partie => canAccessPartie(partie, user));
    }
    // Pour les autres rôles, retourner toutes les parties
    return parties;
}; 