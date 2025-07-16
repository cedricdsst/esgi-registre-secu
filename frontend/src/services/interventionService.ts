import api from './api';

export interface TypeIntervention {
    id: number;
    nom: string;
    ordre_priorite?: number;
    description?: string;
}

export interface Intervenant {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    organisation: string;
}

export interface CreateInterventionData {
    intitule: string;
    entreprise_nom: string;
    intervenant_nom: string;
    type_intervention_id: number;
    partie_ids: number[];
}

export interface Intervention {
    id: number;
    intitule: string;
    entreprise_nom: string;
    intervenant_nom: string;
    type_intervention_id: number;
    statut: 'planifie' | 'en_cours' | 'termine' | 'annule';
    signed_at?: string;
    signed_by?: string;
    created_at: string;
    updated_at: string;
    created_by?: number;
    type_intervention: TypeIntervention;
    parties: any[];
    rapports?: any[];
}

export const interventionService = {
    // Récupérer tous les types d'interventions
    getTypesInterventions: (): Promise<TypeIntervention[]> =>
        api.get('/types-interventions').then(res => res.data?.data || res.data),

    // Récupérer tous les utilisateurs intervenants
    getIntervenants: (): Promise<Intervenant[]> =>
        api.get('/interventions/intervenants').then(res => res.data),

    // Créer une intervention
    create: (data: CreateInterventionData): Promise<Intervention> =>
        api.post('/interventions', data).then(res => res.data.data || res.data),

    // Récupérer toutes les interventions
    getAll: (): Promise<Intervention[]> =>
        api.get('/interventions').then(res => res.data),

    // Récupérer une intervention par ID
    getById: (id: number): Promise<Intervention> =>
        api.get(`/interventions/${id}`).then(res => res.data.data),

    // Mettre à jour une intervention
    update: (id: number, data: Partial<CreateInterventionData>): Promise<Intervention> =>
        api.put(`/interventions/${id}`, data).then(res => res.data.data || res.data),

    // Supprimer une intervention
    delete: (id: number): Promise<void> =>
        api.delete(`/interventions/${id}`).then(res => res.data),

    // Mettre à jour le statut d'une intervention
    updateStatus: (id: number, statut: string): Promise<Intervention> =>
        api.put(`/interventions/${id}/status`, { statut }).then(res => res.data.data || res.data),

    // Signer une intervention
    sign: (id: number, signedBy: string): Promise<Intervention> =>
        api.post(`/interventions/${id}/sign`, { signed_by: signedBy }).then(res => res.data.data || res.data),
}; 