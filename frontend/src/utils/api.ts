import axios from 'axios';
import type { ApiResponse, ApiError } from '../types';

// Configuration de l'instance Axios
const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('authToken');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Intercepteur pour gérer les réponses et les erreurs
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Gestion des erreurs d'authentification
        if (error.response?.status === 401) {
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        // Gestion des erreurs de validation
        if (error.response?.status === 422) {
            const apiError: ApiError = {
                message: error.response.data.message || 'Erreur de validation',
                errors: error.response.data.errors,
            };
            return Promise.reject(apiError);
        }

        // Autres erreurs
        const apiError: ApiError = {
            message: error.response?.data?.message || 'Une erreur est survenue',
        };
        return Promise.reject(apiError);
    }
);

// Fonctions utilitaires pour les appels API

// GET
export const get = async function <T>(url: string, params?: any): Promise<T> {
    const response = await api.get(url, { params });
    return response.data;
};

// POST
export const post = async function <T>(url: string, data?: any): Promise<T> {
    const response = await api.post(url, data);
    return response.data;
};

// PUT
export const put = async function <T>(url: string, data?: any): Promise<T> {
    const response = await api.put(url, data);
    return response.data;
};

// DELETE
export const del = async function <T>(url: string): Promise<T> {
    const response = await api.delete(url);
    return response.data;
};

// PATCH
export const patch = async function <T>(url: string, data?: any): Promise<T> {
    const response = await api.patch(url, data);
    return response.data;
};

// Upload de fichiers
export const uploadFile = async function <T>(url: string, file: File, onProgress?: (progress: number) => void): Promise<T> {
    const formData = new FormData();
    formData.append('file', file);

    const response = await api.post(url, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
            if (onProgress && progressEvent.total) {
                const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                onProgress(progress);
            }
        },
    });

    return response.data;
};

// Téléchargement de fichiers
export const downloadFile = async (url: string, filename: string): Promise<void> => {
    const response = await api.get(url, {
        responseType: 'blob',
    });

    const blob = new Blob([response.data]);
    const downloadUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(downloadUrl);
};

// Fonction pour gérer les erreurs d'API
export const handleApiError = (error: any): string => {
    if (error.errors) {
        // Erreurs de validation
        const messages = Object.values(error.errors).flat() as string[];
        return messages.join(', ');
    }

    return error.message || 'Une erreur est survenue';
};

// Fonction pour formater les paramètres d'URL
export const formatQueryParams = (params: Record<string, any>): string => {
    const searchParams = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            if (Array.isArray(value)) {
                value.forEach((item) => searchParams.append(`${key}[]`, item));
            } else {
                searchParams.append(key, value.toString());
            }
        }
    });

    return searchParams.toString();
};

export default api; 