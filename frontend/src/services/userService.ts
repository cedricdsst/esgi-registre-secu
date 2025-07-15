import api from './api';
import type { User } from '../types';

export interface CreateUserData {
    nom: string;
    prenom: string;
    email: string;
    organisation: string;
    role: 'user-entreprise' | 'user-intervenant';
}

export interface CreateUserResponse {
    message: string;
    user: User;
    temporary_password?: string; // Inclure le mot de passe temporaire si l'email échoue
}

export const userService = {
    // Récupérer tous les utilisateurs (pour l'affichage de la liste)
    getAll: (): Promise<User[]> =>
        api.get('/admin/users').then(res => res.data),

    // Créer un nouvel utilisateur (route admin/register)
    createUser: (userData: CreateUserData): Promise<CreateUserResponse> =>
        api.post('/admin/register', userData).then(res => res.data),

    // Récupérer un utilisateur par ID
    getById: (id: number): Promise<User> =>
        api.get(`/users/${id}`).then(res => res.data),

    // Mettre à jour un utilisateur
    update: (id: number, userData: Partial<CreateUserData>): Promise<User> =>
        api.put(`/users/${id}`, userData).then(res => res.data),

    // Supprimer un utilisateur
    delete: (id: number): Promise<void> =>
        api.delete(`/users/${id}`).then(res => res.data),
}; 