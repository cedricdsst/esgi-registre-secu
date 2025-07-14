import api from '../utils/api';
import type { Site, SiteFormData } from '../types';

export const siteService = {
    // Obtenir tous les sites
    async getAll(): Promise<Site[]> {
        const response = await api.get('/sites');
        return response.data;
    },

    // Obtenir un site par ID
    async getById(id: number): Promise<{ site: Site }> {
        const response = await api.get(`/sites/${id}`);
        return response.data;
    },

    // Créer un nouveau site
    async create(data: SiteFormData): Promise<{ message: string; site: Site }> {
        const response = await api.post('/sites', data);
        return response.data;
    },

    // Mettre à jour un site
    async update(id: number, data: Partial<SiteFormData>): Promise<{ message: string; site: Site }> {
        const response = await api.put(`/sites/${id}`, data);
        return response.data;
    },

    // Supprimer un site
    async delete(id: number): Promise<{ message: string }> {
        const response = await api.delete(`/sites/${id}`);
        return response.data;
    },

    // Obtenir les bâtiments d'un site
    async getBatiments(siteId: number) {
        const response = await api.get(`/sites/${siteId}/batiments`);
        return response.data;
    }
}; 