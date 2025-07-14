import api from '../utils/api';
import type { AuthResponse, LoginData, RegisterData, User } from '../types';

export const authService = {
    // Connexion
    async login(data: LoginData): Promise<AuthResponse> {
        const response = await api.post('/login', data);
        return response.data;
    },

    // Inscription
    async register(data: RegisterData): Promise<AuthResponse> {
        const response = await api.post('/register', data);
        return response.data;
    },

    // Obtenir les informations de l'utilisateur connecté
    async getMe(): Promise<{ user: User }> {
        const response = await api.get('/me');
        return response.data;
    },

    // Déconnexion
    async logout(): Promise<{ message: string }> {
        const response = await api.post('/logout');
        return response.data;
    },

    // Déconnexion de tous les appareils
    async logoutAll(): Promise<{ message: string }> {
        const response = await api.post('/logout-all');
        return response.data;
    },

    // Stocker le token et les informations utilisateur
    setAuthData(token: string, user: User): void {
        localStorage.setItem('authToken', token);
        localStorage.setItem('user', JSON.stringify(user));
    },

    // Récupérer le token
    getToken(): string | null {
        return localStorage.getItem('authToken');
    },

    // Récupérer les informations utilisateur
    getUser(): User | null {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    },

    // Vérifier si l'utilisateur est connecté
    isAuthenticated(): boolean {
        return !!this.getToken();
    },

    // Supprimer les données d'authentification
    clearAuthData(): void {
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
    }
}; 