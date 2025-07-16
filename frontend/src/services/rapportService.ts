import api from './api';

export interface TypeRapport {
    id: number;
    nom: string;
    libelle: string;
    sous_titre?: string;
    periodicite: string;
    typologie_batiment: string;
    organisme_agree_requis: boolean;
    periodicite_en_mois?: number;
}

export interface Observation {
    id?: number;
    identification: string;
    libelle: string;
    localisation: string;
    priorite: 'urgent' | 'normal' | 'faible';
    statut_traitement: 'nouveau' | 'en_cours' | 'traite' | 'reporte';
    deja_signalee: boolean;
    date_signalement_precedent?: string;
    partie_ids?: number[];
}

export interface CreateRapportData {
    intervention_id: number;
    type_rapport_id: number;
    date_emission: string;
    equipements_selection?: number[];
    partie_ids: number[];
    observations: Omit<Observation, 'id'>[];
}

export interface Rapport {
    id: number;
    intervention_id: number;
    type_rapport_id: number;
    date_emission: string;
    statut: 'brouillon' | 'finalise' | 'signe' | 'archive';
    equipements_selection?: number[];
    created_at: string;
    updated_at: string;
    type_rapport: TypeRapport;
    intervention?: {
        id: number;
        intitule: string;
        entreprise_nom: string;
        intervenant_nom: string;
        statut: string;
        created_at: string;
        updated_at: string;
    };
    observations?: Observation[];
    fichiers?: any[];
    parties?: any[];
}

export const rapportService = {
    // Récupérer tous les types de rapports
    getTypesRapports: (): Promise<TypeRapport[]> =>
        api.get('/types-rapports').then(res => res.data.data || res.data),

    // Créer un rapport
    create: (data: CreateRapportData): Promise<Rapport> =>
        api.post('/rapports', data).then(res => res.data.data || res.data),

    // Récupérer tous les rapports
    getAll: (): Promise<Rapport[]> =>
        api.get('/rapports').then(res => res.data.data || res.data),

    // Récupérer un rapport par ID
    getById: (id: number): Promise<Rapport> =>
        api.get(`/rapports/${id}`).then(res => res.data.data || res.data),

    // Mettre à jour un rapport
    update: (id: number, data: Partial<CreateRapportData>): Promise<Rapport> =>
        api.put(`/rapports/${id}`, data).then(res => res.data.data || res.data),

    // Supprimer un rapport
    delete: (id: number): Promise<void> =>
        api.delete(`/rapports/${id}`).then(res => res.data),

    // Signer un rapport
    sign: (id: number, signedBy: string): Promise<Rapport> =>
        api.post(`/rapports/${id}/sign`, { signed_by: signedBy }).then(res => res.data.data || res.data),

    // Archiver un rapport
    archive: (id: number): Promise<Rapport> =>
        api.post(`/rapports/${id}/archive`).then(res => res.data.data || res.data),

    // Upload de fichiers pour un rapport
    uploadFile: (rapportId: number, file: File): Promise<any> => {
        const formData = new FormData();
        formData.append('file', file);
        return api.post(`/rapports/${rapportId}/files`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        }).then(res => res.data);
    },

    // Télécharger un fichier
    downloadFile: (rapportId: number, fileId: number): Promise<Blob> =>
        api.get(`/rapports/${rapportId}/files/${fileId}`, {
            responseType: 'blob',
        }).then(res => res.data),

    // Supprimer un fichier
    deleteFile: (rapportId: number, fileId: number): Promise<void> =>
        api.delete(`/rapports/${rapportId}/files/${fileId}`).then(res => res.data),
}; 