import api from '../utils/api';
import type { Entreprise, EntrepriseFormData } from '../types';

export const entrepriseService = {
    // Obtenir toutes les entreprises
    async getAll(): Promise<Entreprise[]> {
        const response = await api.get('/entreprises');
        return response.data;
    },

    // Obtenir une entreprise par ID
    async getById(id: number): Promise<{ entreprise: Entreprise }> {
        const response = await api.get(`/entreprises/${id}`);
        return response.data;
    },

    // Créer une nouvelle entreprise
    async create(data: EntrepriseFormData): Promise<{ message: string; entreprise: Entreprise }> {
        const response = await api.post('/entreprises', data);
        return response.data;
    },

    // Mettre à jour une entreprise
    async update(id: number, data: Partial<EntrepriseFormData>): Promise<{ message: string; entreprise: Entreprise }> {
        const response = await api.put(`/entreprises/${id}`, data);
        return response.data;
    },

    // Supprimer une entreprise
    async delete(id: number): Promise<{ message: string }> {
        const response = await api.delete(`/entreprises/${id}`);
        return response.data;
    }
}; 