import api from '../utils/api';
import type { Batiment, BatimentFormData } from '../types';

export const batimentService = {
    // Obtenir tous les bâtiments
    async getAll(): Promise<Batiment[]> {
        const response = await api.get('/batiments');
        return response.data;
    },

    // Obtenir un bâtiment par ID
    async getById(id: number): Promise<{ batiment: Batiment }> {
        const response = await api.get(`/batiments/${id}`);
        return response.data;
    },

    // Créer un nouveau bâtiment
    async create(data: BatimentFormData): Promise<{ message: string; batiment: Batiment }> {
        const response = await api.post('/batiments', data);
        return response.data;
    },

    // Mettre à jour un bâtiment
    async update(id: number, data: Partial<BatimentFormData>): Promise<{ message: string; batiment: Batiment }> {
        const response = await api.put(`/batiments/${id}`, data);
        return response.data;
    },

    // Supprimer un bâtiment
    async delete(id: number): Promise<{ message: string }> {
        const response = await api.delete(`/batiments/${id}`);
        return response.data;
    },

    // Obtenir les niveaux d'un bâtiment
    async getNiveaux(batimentId: number) {
        const response = await api.get(`/batiments/${batimentId}/niveaux`);
        return response.data;
    },

    // Obtenir les parties d'un bâtiment
    async getParties(batimentId: number) {
        const response = await api.get(`/batiments/${batimentId}/parties`);
        return response.data;
    }
}; 