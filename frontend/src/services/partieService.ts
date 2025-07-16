import api from '../utils/api';
import type { Partie, PartieFormData, Niveau, User } from '../types';

export const partieService = {
    // Obtenir toutes les parties
    async getAll(filters?: { batiment_id?: number; niveau_id?: number }): Promise<Partie[]> {
        const params = new URLSearchParams();
        if (filters?.batiment_id) params.append('batiment_id', filters.batiment_id.toString());
        if (filters?.niveau_id) params.append('niveau_id', filters.niveau_id.toString());

        const response = await api.get(`/parties?${params}`);
        return response.data;
    },

    // Obtenir une partie par ID
    async getById(id: number): Promise<Partie> {
        const response = await api.get(`/parties/${id}`);
        return response.data;
    },

    // Créer une nouvelle partie
    async create(data: PartieFormData & { niveaux?: { niveau_id: number; libelle?: string; effectif_public?: number; personnel?: number; surface_exploitation?: number; surface_gla?: number; surface_accessible_public?: number }[] }): Promise<Partie> {
        // Transformer les données pour l'API
        const apiData = {
            ...data,
            // Mapper les champs frontend -> backend
            isICPE: data.est_icpe || false,
            // Extraire les IDs des niveaux
            niveau_ids: data.niveaux?.map(n => n.niveau_id) || [],
            // Données détaillées pour chaque niveau
            niveaux_data: data.niveaux || []
        };

        // Supprimer les champs frontend non utilisés par l'API
        delete apiData.est_icpe;

        // Supprimer le champ niveaux original
        delete apiData.niveaux;

        const response = await api.post('/parties', apiData);
        return response.data;
    },

    // Mettre à jour une partie
    async update(id: number, data: Partial<PartieFormData> & { niveaux?: { niveau_id: number; libelle?: string; effectif_public?: number; personnel?: number; surface_exploitation?: number; surface_gla?: number; surface_accessible_public?: number }[] }): Promise<Partie> {
        const response = await api.put(`/parties/${id}`, data);
        return response.data;
    },

    // Supprimer une partie
    async delete(id: number): Promise<void> {
        const response = await api.delete(`/parties/${id}`);
        return response.data;
    },

    // Obtenir les parties d'un bâtiment
    async getByBatiment(batimentId: number): Promise<Partie[]> {
        const response = await api.get(`/batiments/${batimentId}/parties-with-owners`);
        return response.data.parties; // Fix: return the parties array from the response object
    },

    // Obtenir les parties d'un niveau
    async getByNiveau(niveauId: number): Promise<Partie[]> {
        const response = await api.get(`/niveaux/${niveauId}/parties`);
        return response.data;
    },

    // **NOUVELLES MÉTHODES POUR LA GESTION DES PROPRIÉTAIRES**

    // Récupérer les utilisateurs entreprise
    async getEntrepriseUsers(): Promise<User[]> {
        const response = await api.get('/parties/entreprise-users');
        return response.data;
    },

    // Assigner un propriétaire à une partie
    async assignOwner(partieId: number, ownerId: number | null): Promise<{ message: string; partie: Partie }> {
        const response = await api.post(`/parties/${partieId}/assign-owner`, {
            owner_id: ownerId
        });
        return response.data;
    },

    // Assigner un propriétaire à plusieurs parties
    async assignOwnerBulk(ownerId: number, partieIds: number[]): Promise<{ message: string; parties: Partie[] }> {
        const response = await api.post('/parties/assign-owner-bulk', {
            owner_id: ownerId,
            partie_ids: partieIds
        });
        return response.data;
    },

    // Récupérer les parties d'un bâtiment avec leurs propriétaires
    async getPartiesByBatimentWithOwners(batimentId: number): Promise<{ parties: Partie[] }> {
        const response = await api.get(`/batiments/${batimentId}/parties-with-owners`);
        return response.data;
    },

    // Attacher un lot à une partie
    async attachLot(partieId: number, lotId: number): Promise<void> {
        await api.post(`/parties/${partieId}/lots/attach`, { lot_id: lotId });
    },

    // Détacher un lot d'une partie
    async detachLot(partieId: number, lotId: number): Promise<void> {
        await api.post(`/parties/${partieId}/lots/detach`, { lot_id: lotId });
    },

    // Transférer un lot vers une autre partie
    async transferLot(partieId: number, lotId: number, targetPartieId: number): Promise<void> {
        await api.post(`/parties/${partieId}/lots/transfer`, {
            lot_id: lotId,
            target_partie_id: targetPartieId
        });
    }
}; 