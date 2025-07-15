import axios from 'axios';
import type { Site, Building, Level, Part, Report, Observation, Intervention, User } from '../types';

// Configuration Axios
const api = axios.create({
    baseURL: 'http://localhost:8000/api',
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

// Intercepteur pour gérer les erreurs de réponse
api.interceptors.response.use(
    (response) => response,
    (error) => {
        // Ne pas rediriger automatiquement, laisser le contexte AuthContext gérer les erreurs 401
        // if (error.response?.status === 401) {
        //     localStorage.removeItem('authToken');
        //     localStorage.removeItem('user');
        //     window.location.href = '/login';
        // }
        return Promise.reject(error);
    }
);

// Auth Service
export const authService = {
    login: (email: string, password: string): Promise<{ user: User; token: string }> =>
        api.post('/login', { email, password }).then(res => res.data),

    register: (userData: {
        nom: string;
        prenom: string;
        email: string;
        password: string;
        password_confirmation: string;
        organisation: string;
        role: string;
    }): Promise<{ user: User; token: string }> =>
        api.post('/register', userData).then(res => res.data),

    logout: (): Promise<void> =>
        api.post('/logout').then(res => res.data),

    me: (): Promise<User> =>
        api.get('/me').then(res => res.data.user), // Extraire l'objet user de la réponse
};

// Site Service
export const siteService = {
    getAll: (): Promise<Site[]> =>
        api.get('/sites').then(res => res.data),

    getById: (id: number): Promise<Site> =>
        api.get(`/sites/${id}`).then(res => res.data.site),

    create: (site: Omit<Site, 'id' | 'created_at' | 'updated_at'>): Promise<Site> =>
        api.post('/sites', site).then(res => res.data.site),

    update: (id: number, site: Partial<Site>): Promise<Site> =>
        api.put(`/sites/${id}`, site).then(res => res.data.site),

    delete: (id: number): Promise<void> =>
        api.delete(`/sites/${id}`).then(res => res.data),

    getBatiments: (id: number): Promise<Building[]> =>
        api.get(`/sites/${id}/batiments`).then(res => res.data),
};

// Building Service
export const buildingService = {
    getAll: (siteId?: number): Promise<Building[]> =>
        api.get(siteId ? `/batiments?site_id=${siteId}` : '/batiments').then(res =>
            res.data.map((building: any) => ({
                ...building,
                nom: building.name,
                typologie: building.type
            }))
        ),

    getById: (id: number): Promise<Building> =>
        api.get(`/batiments/${id}`).then(res => {
            // L'API retourne { batiment: BatimentResource }
            const building = res.data.batiment;
            return {
                ...building,
                nom: building.name,
                typologie: building.type
            };
        }),

    create: (building: Omit<Building, 'id' | 'created_at' | 'updated_at'>): Promise<Building> => {
        // Mapper les données du frontend vers le backend
        const backendData = {
            site_id: building.site_id,
            name: building.nom,
            type: building.typologie,
            isICPE: building.typologie === 'ICPE'
        };
        return api.post('/batiments', backendData).then(res => {
            // Mapper les données du backend vers le frontend
            const backendBuilding = res.data.batiment;
            return {
                ...backendBuilding,
                nom: backendBuilding.name,
                typologie: backendBuilding.type
            };
        });
    },

    update: (id: number, building: Partial<Building>): Promise<Building> => {
        // Mapper les données du frontend vers le backend
        const backendData: any = {};
        if (building.nom) backendData.name = building.nom;
        if (building.typologie) backendData.type = building.typologie;

        return api.put(`/batiments/${id}`, backendData).then(res => {
            const backendBuilding = res.data.batiment;
            return {
                ...backendBuilding,
                nom: backendBuilding.name,
                typologie: backendBuilding.type
            };
        });
    },

    delete: (id: number): Promise<void> =>
        api.delete(`/batiments/${id}`).then(res => res.data),

    getNiveaux: (id: number): Promise<Level[]> =>
        api.get(`/batiments/${id}/niveaux`).then(res => res.data),

    getParties: (id: number): Promise<Part[]> =>
        api.get(`/batiments/${id}/parties`).then(res => res.data),
};

// Level Service
export const levelService = {
    getAll: (batimentId?: number): Promise<Level[]> =>
        api.get(batimentId ? `/niveaux?batiment_id=${batimentId}` : '/niveaux').then(res => res.data),

    getById: (id: number): Promise<Level> =>
        api.get(`/niveaux/${id}`).then(res => res.data),

    create: (level: Omit<Level, 'id' | 'created_at' | 'updated_at'>): Promise<Level> =>
        api.post('/niveaux', level).then(res => res.data.niveau),

    update: (id: number, level: Partial<Level>): Promise<Level> =>
        api.put(`/niveaux/${id}`, level).then(res => res.data),

    delete: (id: number): Promise<void> =>
        api.delete(`/niveaux/${id}`).then(res => res.data),

    getParties: (id: number): Promise<Part[]> =>
        api.get(`/niveaux/${id}/parties`).then(res => res.data),
};

// Part Service
export const partService = {
    getAll: (batimentId?: number, niveauId?: number): Promise<Part[]> => {
        let url = '/parties';
        const params = new URLSearchParams();
        if (batimentId) params.append('batiment_id', batimentId.toString());
        if (niveauId) params.append('niveau_id', niveauId.toString());
        if (params.toString()) url += `?${params.toString()}`;
        return api.get(url).then(res => res.data);
    },

    getById: (id: number): Promise<Part> =>
        api.get(`/parties/${id}`).then(res => res.data),

    create: (part: Omit<Part, 'id' | 'created_at' | 'updated_at'>): Promise<Part> =>
        api.post('/parties', part).then(res => res.data),

    update: (id: number, part: Partial<Part>): Promise<Part> =>
        api.put(`/parties/${id}`, part).then(res => res.data),

    delete: (id: number): Promise<void> =>
        api.delete(`/parties/${id}`).then(res => res.data),
};

// Report Service
export const reportService = {
    getAll: (filters?: {
        siteId?: number;
        batimentId?: number;
        partieId?: number;
        interventionId?: number;
        statut?: string;
    }): Promise<Report[]> => {
        let url = '/rapports';
        const params = new URLSearchParams();
        if (filters?.siteId) params.append('site_id', filters.siteId.toString());
        if (filters?.batimentId) params.append('batiment_id', filters.batimentId.toString());
        if (filters?.partieId) params.append('partie_id', filters.partieId.toString());
        if (filters?.interventionId) params.append('intervention_id', filters.interventionId.toString());
        if (filters?.statut) params.append('statut', filters.statut);
        if (params.toString()) url += `?${params.toString()}`;
        return api.get(url).then(res => res.data);
    },

    getById: (id: number): Promise<Report> =>
        api.get(`/rapports/${id}`).then(res => res.data),

    create: (report: Omit<Report, 'id' | 'created_at' | 'updated_at'>): Promise<Report> =>
        api.post('/rapports', report).then(res => res.data),

    update: (id: number, report: Partial<Report>): Promise<Report> =>
        api.put(`/rapports/${id}`, report).then(res => res.data),

    delete: (id: number): Promise<void> =>
        api.delete(`/rapports/${id}`).then(res => res.data),
};

// Observation Service
export const observationService = {
    getAll: (filters?: {
        rapportId?: number;
        partieId?: number;
        statut?: string;
        priorite?: string;
    }): Promise<Observation[]> => {
        let url = '/observations';
        const params = new URLSearchParams();
        if (filters?.rapportId) params.append('rapport_id', filters.rapportId.toString());
        if (filters?.partieId) params.append('partie_id', filters.partieId.toString());
        if (filters?.statut) params.append('statut_traitement', filters.statut);
        if (filters?.priorite) params.append('priorite', filters.priorite);
        if (params.toString()) url += `?${params.toString()}`;
        return api.get(url).then(res => res.data);
    },

    getById: (id: number): Promise<Observation> =>
        api.get(`/observations/${id}`).then(res => res.data),

    create: (observation: Omit<Observation, 'id' | 'created_at' | 'updated_at'>): Promise<Observation> =>
        api.post('/observations', observation).then(res => res.data),

    update: (id: number, observation: Partial<Observation>): Promise<Observation> =>
        api.put(`/observations/${id}`, observation).then(res => res.data),

    delete: (id: number): Promise<void> =>
        api.delete(`/observations/${id}`).then(res => res.data),
};

// Intervention Service
export const interventionService = {
    getAll: (filters?: {
        partieId?: number;
        typeInterventionId?: number;
        statut?: string;
    }): Promise<Intervention[]> => {
        let url = '/interventions';
        const params = new URLSearchParams();
        if (filters?.partieId) params.append('partie_id', filters.partieId.toString());
        if (filters?.typeInterventionId) params.append('type_intervention_id', filters.typeInterventionId.toString());
        if (filters?.statut) params.append('statut', filters.statut);
        if (params.toString()) url += `?${params.toString()}`;
        return api.get(url).then(res => res.data);
    },

    getById: (id: number): Promise<Intervention> =>
        api.get(`/interventions/${id}`).then(res => res.data),

    create: (intervention: Omit<Intervention, 'id' | 'created_at' | 'updated_at'>): Promise<Intervention> =>
        api.post('/interventions', intervention).then(res => res.data),

    update: (id: number, intervention: Partial<Intervention>): Promise<Intervention> =>
        api.put(`/interventions/${id}`, intervention).then(res => res.data),

    delete: (id: number): Promise<void> =>
        api.delete(`/interventions/${id}`).then(res => res.data),
};

// Export du service utilisateur
export { userService } from './userService';

export default api; 