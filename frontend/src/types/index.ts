// Types d'authentification
export interface User {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    role: string;
    organisation: string;
    roles: string[];
    permissions: string[];
}

export interface AuthResponse {
    message: string;
    user: User;
    token: string;
}

export interface LoginData {
    email: string;
    password: string;
}

export interface RegisterData {
    nom: string;
    prenom: string;
    email: string;
    password: string;
    password_confirmation: string;
    organisation: string;
    role?: string;
}

// Types pour les sites
export interface Site {
    id: number;
    nom: string;
    adresse: string;
    code_postal: string;
    ville: string;
    pays: string;
    description?: string;
    client_id: number;
    batiments?: Batiment[];
    client?: User;
    created_at: string;
    updated_at: string;
}

export interface SiteFormData {
    nom: string;
    adresse: string;
    code_postal: string;
    ville: string;
    pays?: string;
    description?: string;
    client_id?: number;
}

// Types pour les bâtiments
export interface Batiment {
    id: number;
    nom: string;
    site_id: number;
    typologie: 'ERP' | 'IGH' | 'HAB' | 'BUP' | 'ICPE';
    description?: string;
    site?: Site;
    niveaux?: Niveau[];
    parties?: Partie[];
    created_at: string;
    updated_at: string;
}

export interface BatimentFormData {
    nom: string;
    site_id: number;
    typologie: 'ERP' | 'IGH' | 'HAB' | 'BUP' | 'ICPE';
    description?: string;
}

// Types pour les niveaux
export interface Niveau {
    id: number;
    nom: string;
    batiment_id: number;
    numero_etage: number;
    description?: string;
    batiment?: Batiment;
    parties?: Partie[];
    created_at: string;
    updated_at: string;
}

export interface NiveauFormData {
    nom: string;
    batiment_id: number;
    numero_etage: number;
    description?: string;
}

// Type pour la gestion temporaire des niveaux dans les formulaires
export interface NiveauTemp {
    id: string; // ID temporaire pour la gestion locale
    nom: string;
    numero_etage: number;
    description?: string;
}

// Types pour les parties
export interface Partie {
    id: number;
    nom: string;
    batiment_id: number;
    type: 'privatives' | 'communes';
    description?: string;
    est_icpe: boolean;
    effectif_public?: number;
    effectif_personnel?: number;
    surface_exploitation?: number;
    surface_gla?: number;
    surface_accessible_public?: number;
    batiment?: Batiment;
    niveaux?: Niveau[];
    lots?: Lot[];
    created_at: string;
    updated_at: string;
}

export interface PartieFormData {
    nom: string;
    batiment_id: number;
    type: 'privatives' | 'communes';
    description?: string;
    est_icpe?: boolean;
    effectif_public?: number;
    effectif_personnel?: number;
    surface_exploitation?: number;
    surface_gla?: number;
    surface_accessible_public?: number;
}

// Types pour les lots
export interface Lot {
    id: number;
    nom: string;
    batiment_id: number;
    description?: string;
    batiment?: Batiment;
    parties?: Partie[];
    created_at: string;
    updated_at: string;
}

export interface LotFormData {
    nom: string;
    batiment_id: number;
    description?: string;
}

// Types pour les entreprises
export interface Entreprise {
    id: number;
    nom: string;
    adresse: string;
    code_postal: string;
    ville: string;
    pays: string;
    telephone?: string;
    email?: string;
    siret?: string;
    est_organisme_agree: boolean;
    created_at: string;
    updated_at: string;
}

export interface EntrepriseFormData {
    nom: string;
    adresse: string;
    code_postal: string;
    ville: string;
    pays?: string;
    telephone?: string;
    email?: string;
    siret?: string;
    est_organisme_agree?: boolean;
}

// Types pour les types d'interventions
export interface TypeIntervention {
    id: number;
    nom: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export interface TypeInterventionFormData {
    nom: string;
    description?: string;
}

// Types pour les interventions
export interface Intervention {
    id: number;
    intitule: string;
    entreprise_id: number;
    nom_intervenant: string;
    type_intervention_id: number;
    statut: 'en_cours' | 'terminee' | 'en_attente';
    date_debut: string;
    date_fin?: string;
    signature_intervenant?: string;
    entreprise?: Entreprise;
    type_intervention?: TypeIntervention;
    parties?: Partie[];
    created_at: string;
    updated_at: string;
}

export interface InterventionFormData {
    intitule: string;
    entreprise_id: number;
    nom_intervenant: string;
    type_intervention_id: number;
    statut?: 'en_cours' | 'terminee' | 'en_attente';
    date_debut: string;
    date_fin?: string;
    parties?: number[];
}

// Types pour les types de rapports
export interface TypeRapport {
    id: number;
    nom: string;
    sous_titre?: string;
    periodicite: string;
    typologie_batiment?: string;
    organisme_agree_requis: boolean;
    created_at: string;
    updated_at: string;
}

export interface TypeRapportFormData {
    nom: string;
    sous_titre?: string;
    periodicite: string;
    typologie_batiment?: string;
    organisme_agree_requis?: boolean;
}

// Types pour les rapports
export interface Rapport {
    id: number;
    type_rapport_id: number;
    intervention_id?: number;
    date_emission: string;
    statut: 'brouillon' | 'finalise' | 'archive';
    equipements_techniques?: string;
    type_rapport?: TypeRapport;
    intervention?: Intervention;
    parties?: Partie[];
    observations?: Observation[];
    created_at: string;
    updated_at: string;
}

export interface RapportFormData {
    type_rapport_id: number;
    intervention_id?: number;
    date_emission: string;
    statut?: 'brouillon' | 'finalise' | 'archive';
    equipements_techniques?: string;
    parties?: number[];
}

// Types pour les observations
export interface Observation {
    id: number;
    rapport_id: number;
    identification: string;
    libelle: string;
    localisation?: string;
    priorite_traitement?: 'faible' | 'moyenne' | 'haute' | 'critique';
    statut_traitement: 'non_traite' | 'en_cours' | 'traite' | 'reporte';
    deja_signalee: boolean;
    date_signalement?: string;
    rapport?: Rapport;
    parties?: Partie[];
    fichiers?: FichierObservation[];
    created_at: string;
    updated_at: string;
}

export interface ObservationFormData {
    rapport_id: number;
    identification: string;
    libelle: string;
    localisation?: string;
    priorite_traitement?: 'faible' | 'moyenne' | 'haute' | 'critique';
    statut_traitement?: 'non_traite' | 'en_cours' | 'traite' | 'reporte';
    deja_signalee?: boolean;
    date_signalement?: string;
    parties?: number[];
}

// Types pour les fichiers d'observation
export interface FichierObservation {
    id: number;
    observation_id: number;
    nom_fichier: string;
    chemin_fichier: string;
    type_mime: string;
    taille: number;
    observation?: Observation;
    created_at: string;
    updated_at: string;
}

// Types pour les inventaires
export interface InventairePartie {
    id: number;
    partie_id: number;
    nom: string;
    description?: string;
    donnees_inventaire?: any;
    partie?: Partie;
    created_at: string;
    updated_at: string;
}

export interface InventairePartieFormData {
    partie_id: number;
    nom: string;
    description?: string;
    donnees_inventaire?: any;
}

// Types pour les réponses d'API
export interface ApiResponse<T> {
    data: T;
    message?: string;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

// Types pour la pagination
export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

// Types pour les filtres
export interface FilterParams {
    page?: number;
    per_page?: number;
    search?: string;
    sort?: string;
    order?: 'asc' | 'desc';
    [key: string]: any;
}

// Alias pour compatibilité avec les noms anglais
export type Building = Batiment;
export type Level = Niveau;
export type Part = Partie;
export type Report = Rapport; 