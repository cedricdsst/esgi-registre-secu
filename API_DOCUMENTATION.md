# Documentation API - Registre de Sécurité ESGI

## 📋 Vue d'ensemble

Cette API REST basée sur Laravel 11 permet la gestion complète d'un système de registre de sécurité pour bâtiments. Elle inclut la gestion des sites, bâtiments, niveaux, parties, interventions, rapports et observations.

## 🔐 Authentification

### Configuration
- **Système** : Laravel Sanctum (tokens API)
- **Gestion des permissions** : Spatie Permission
- **Vérification email** : Obligatoire pour l'accès

### Endpoints d'authentification

#### `POST /api/login`
**Connexion utilisateur**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse de succès (200)**
```json
{
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@example.com",
    "role": "user",
    "organisation": "ESGI",
    "roles": ["user"],
    "permissions": ["view_sites", "create_sites", ...]
  },
  "token": "1|abc123..."
}
```

#### `POST /api/register`
**Inscription utilisateur**
```json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "organisation": "ESGI",
  "role": "user"
}
```

#### `GET /api/me`
**Informations utilisateur connecté** (Auth requis)

#### `POST /api/logout`
**Déconnexion** (Auth requis)

#### `POST /api/logout-all`
**Déconnexion de tous les appareils** (Auth requis)

## 🏗️ Structure hiérarchique des données

```
Site
├── Bâtiments
    ├── Niveaux
        └── Parties
            └── Lots
```

## 🌐 Endpoints principaux

> ⚠️ **Toutes les routes suivantes nécessitent une authentification Bearer Token**

### 🏢 Sites

#### `GET /api/sites`
**Liste des sites**
- **Filtres** : Aucun filtre spécifique
- **Relations** : `batiments`, `droitsSite`, `client`

#### `POST /api/sites`
**Créer un site**
```json
{
  "nom": "Site Principal",
  "adresse": "123 Rue de la Paix",
  "code_postal": "75001",
  "ville": "Paris",
  "pays": "France",
  "description": "Description du site",
  "client_id": 1
}
```

#### `GET /api/sites/{id}`
**Détails d'un site**

#### `PUT /api/sites/{id}`
**Mettre à jour un site**

#### `DELETE /api/sites/{id}`
**Supprimer un site**

#### `GET /api/sites/{id}/batiments`
**Bâtiments d'un site spécifique**

**Format de réponse Site**
```json
{
  "id": 1,
  "nom": "Site Principal",
  "adresse": "123 Rue de la Paix",
  "code_postal": "75001",
  "ville": "Paris",
  "pays": "France",
  "description": "Description du site",
  "client_id": 1,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "deleted_at": null,
  "client": {
    "id": 1,
    "nom": "Client",
    "prenom": "Prénom",
    "email": "client@example.com",
    "organisation": "Organisation"
  },
  "batiments": [...],
  "droits_site": [...],
  "stats": {
    "nombre_batiments": 5,
    "nombre_niveaux": 25,
    "nombre_parties": 150
  }
}
```

### 🏠 Bâtiments

#### `GET /api/batiments`
**Liste des bâtiments**
- **Filtres** : `?site_id=1`

#### `POST /api/batiments`
**Créer un bâtiment**
```json
{
  "site_id": 1,
  "name": "Bâtiment A",
  "type": "bureaux",
  "isICPE": false
}
```

#### `GET /api/batiments/{id}`
**Détails d'un bâtiment**

#### `PUT /api/batiments/{id}`
**Mettre à jour un bâtiment**

#### `DELETE /api/batiments/{id}`
**Supprimer un bâtiment**

#### `GET /api/batiments/{id}/niveaux`
**Niveaux d'un bâtiment spécifique**

#### `GET /api/batiments/{id}/parties`
**Parties d'un bâtiment spécifique**

**Format de réponse Bâtiment**
```json
{
  "id": 1,
  "site_id": 1,
  "name": "Bâtiment A",
  "type": "bureaux",
  "isICPE": false,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "deleted_at": null,
  "site": {
    "id": 1,
    "nom": "Site Principal",
    "adresse": "123 Rue de la Paix",
    "ville": "Paris"
  },
  "niveaux": [...],
  "droits_batiment": [...],
  "erps": [...],
  "ighs": [...],
  "habs": [...],
  "bups": [...],
  "stats": {
    "nombre_niveaux": 5,
    "nombre_parties": 30,
    "typologie_details": {
      "erps_count": 2,
      "ighs_count": 0,
      "habs_count": 1,
      "bups_count": 0
    }
  }
}
```

### 🏢 Niveaux

#### `GET /api/niveaux`
**Liste des niveaux**
- **Filtres** : `?batiment_id=1`

#### `POST /api/niveaux`
**Créer un niveau**
```json
{
  "batiment_id": 1,
  "nom": "Rez-de-chaussée",
  "numero_etage": 0,
  "description": "Description du niveau"
}
```

#### `GET /api/niveaux/{id}`
**Détails d'un niveau**

#### `PUT /api/niveaux/{id}`
**Mettre à jour un niveau**

#### `DELETE /api/niveaux/{id}`
**Supprimer un niveau**

#### `GET /api/niveaux/{id}/parties`
**Parties d'un niveau spécifique**

### 🏗️ Parties

#### `GET /api/parties`
**Liste des parties**
- **Filtres** : `?batiment_id=1`, `?niveau_id=1`

#### `POST /api/parties`
**Créer une partie**
```json
{
  "batiment_id": 1,
  "nom": "Bureau 101",
  "type": "privative",
  "isICPE": false,
  "isPrivative": true,
  "activites_erp": "Bureaux",
  "niveaux": [
    {
      "niveau_id": 1,
      "libelle": "Bureau principal",
      "effectif_public": 10,
      "personnel": 5,
      "surface_exploitation": 100.5,
      "surface_gla": 95.0,
      "surface_accessible_public": 80.0
    }
  ]
}
```

#### `GET /api/parties/{id}`
**Détails d'une partie**

#### `PUT /api/parties/{id}`
**Mettre à jour une partie**

#### `DELETE /api/parties/{id}`
**Supprimer une partie**

#### `POST /api/parties/{id}/lots/attach`
**Attacher un lot à une partie**
```json
{
  "lot_id": 1
}
```

#### `POST /api/parties/{id}/lots/detach`
**Détacher un lot d'une partie**
```json
{
  "lot_id": 1
}
```

#### `POST /api/parties/{id}/lots/transfer`
**Transférer un lot vers une autre partie**
```json
{
  "lot_id": 1,
  "target_partie_id": 2
}
```

**Format de réponse Partie**
```json
{
  "id": 1,
  "batiment_id": 1,
  "nom": "Bureau 101",
  "type": "privative",
  "isICPE": false,
  "isPrivative": true,
  "activites_erp": "Bureaux",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "deleted_at": null,
  "batiment": {
    "id": 1,
    "name": "Bâtiment A",
    "type": "bureaux",
    "site": {
      "id": 1,
      "nom": "Site Principal"
    }
  },
  "niveaux": [
    {
      "id": 1,
      "nom": "Rez-de-chaussée",
      "numero_etage": 0,
      "description": "Description",
      "donnees_partie": {
        "libelle": "Bureau principal",
        "effectif_public": 10,
        "personnel": 5,
        "surface_exploitation": 100.5,
        "surface_gla": 95.0,
        "surface_accessible_public": 80.0
      }
    }
  ],
  "lots": [...],
  "droits_partie": [...],
  "stats": {
    "nombre_lots": 3,
    "nombre_niveaux": 1,
    "type_display": "Privative"
  }
}
```

### 📦 Lots

#### `GET /api/lots`
**Liste des lots**

#### `POST /api/lots`
**Créer un lot**
```json
{
  "nom": "Lot 1",
  "description": "Description du lot"
}
```

#### `GET /api/lots/{id}`
**Détails d'un lot**

#### `PUT /api/lots/{id}`
**Mettre à jour un lot**

#### `DELETE /api/lots/{id}`
**Supprimer un lot**

### 🏢 Entreprises

#### `GET /api/entreprises`
**Liste des entreprises**

#### `POST /api/entreprises`
**Créer une entreprise**
```json
{
  "nom": "Entreprise ABC",
  "adresse": "123 Rue de l'Entreprise",
  "telephone": "0123456789",
  "email": "contact@entreprise.com"
}
```

#### `GET /api/entreprises/{id}`
**Détails d'une entreprise**

#### `PUT /api/entreprises/{id}`
**Mettre à jour une entreprise**

#### `DELETE /api/entreprises/{id}`
**Supprimer une entreprise**

### 🔧 Types d'interventions

#### `GET /api/types-interventions`
**Liste des types d'interventions**

#### `POST /api/types-interventions`
**Créer un type d'intervention**
```json
{
  "nom": "Maintenance préventive",
  "description": "Interventions de maintenance préventive"
}
```

#### `GET /api/types-interventions/{id}`
**Détails d'un type d'intervention**

#### `PUT /api/types-interventions/{id}`
**Mettre à jour un type d'intervention**

#### `DELETE /api/types-interventions/{id}`
**Supprimer un type d'intervention**

### 🛠️ Interventions

#### `GET /api/interventions`
**Liste des interventions**
- **Filtres** : `?statut=planifie`, `?type_intervention_id=1`, `?partie_id=1`

#### `POST /api/interventions`
**Créer une intervention**
```json
{
  "intitule": "Contrôle des extincteurs",
  "entreprise_nom": "Entreprise ABC",
  "intervenant_nom": "Jean Dupont",
  "type_intervention_id": 1,
  "partie_ids": [1, 2, 3]
}
```

#### `GET /api/interventions/{id}`
**Détails d'une intervention**

#### `PUT /api/interventions/{id}`
**Mettre à jour une intervention**
```json
{
  "intitule": "Contrôle des extincteurs",
  "entreprise_nom": "Entreprise ABC",
  "intervenant_nom": "Jean Dupont",
  "type_intervention_id": 1,
  "statut": "termine",
  "partie_ids": [1, 2, 3]
}
```

#### `DELETE /api/interventions/{id}`
**Supprimer une intervention**

#### `POST /api/interventions/{id}/sign`
**Signer une intervention**
```json
{
  "signed_by": "Jean Dupont - Responsable sécurité"
}
```

**Statuts d'intervention** : `planifie`, `en_cours`, `termine`, `annule`

**Format de réponse Intervention**
```json
{
  "id": 1,
  "intitule": "Contrôle des extincteurs",
  "entreprise_nom": "Entreprise ABC",
  "intervenant_nom": "Jean Dupont",
  "type_intervention_id": 1,
  "statut": "planifie",
  "signed_at": null,
  "signed_by": null,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "type_intervention": {
    "id": 1,
    "nom": "Maintenance préventive"
  },
  "parties": [...],
  "rapports": [...],
  "observations_suivi": [...]
}
```

### 📄 Types de rapports

#### `GET /api/types-rapports`
**Liste des types de rapports**

#### `POST /api/types-rapports`
**Créer un type de rapport**
```json
{
  "nom": "Rapport de vérification",
  "description": "Rapport de vérification périodique"
}
```

#### `GET /api/types-rapports/{id}`
**Détails d'un type de rapport**

#### `PUT /api/types-rapports/{id}`
**Mettre à jour un type de rapport**

#### `DELETE /api/types-rapports/{id}`
**Supprimer un type de rapport**

### 📋 Rapports

#### `GET /api/rapports`
**Liste des rapports**
- **Filtres** : `?intervention_id=1`, `?type_rapport_id=1`, `?statut=brouillon`, `?partie_id=1`

#### `POST /api/rapports`
**Créer un rapport**
```json
{
  "intervention_id": 1,
  "type_rapport_id": 1,
  "titre": "Rapport de contrôle extincteurs",
  "contenu": "Contenu du rapport...",
  "statut": "draft",
  "partie_ids": [1, 2, 3]
}
```

#### `GET /api/rapports/{id}`
**Détails d'un rapport**

#### `PUT /api/rapports/{id}`
**Mettre à jour un rapport**

#### `DELETE /api/rapports/{id}`
**Supprimer un rapport**

#### `POST /api/rapports/{id}/sign`
**Signer un rapport**
```json
{
  "signed_by": "Jean Dupont - Responsable sécurité"
}
```

#### `POST /api/rapports/{id}/archive`
**Archiver un rapport**

**Statuts de rapport** : `brouillon`, `finalise`, `signe`, `archive`

### 🔍 Observations

#### `GET /api/observations`
**Liste des observations**
- **Filtres** : `?rapport_id=1`, `?statut_traitement=nouveau`, `?priorite=urgent`, `?partie_id=1`

#### `POST /api/observations`
**Créer une observation**
```json
{
  "rapport_id": 1,
  "identification": "OBS-001",
  "libelle": "Extincteur manquant",
  "localisation": "Couloir niveau 1",
  "priorite": "urgent",
  "deja_signalee": false,
  "date_signalement_precedent": null,
  "partie_ids": [1, 2]
}
```

#### `GET /api/observations/{id}`
**Détails d'une observation**

#### `PUT /api/observations/{id}`
**Mettre à jour une observation**
```json
{
  "identification": "OBS-001",
  "libelle": "Extincteur manquant - Mis à jour",
  "localisation": "Couloir niveau 1",
  "priorite": "urgent",
  "statut_traitement": "en_cours",
  "deja_signalee": false,
  "date_signalement_precedent": null,
  "partie_ids": [1, 2]
}
```

#### `DELETE /api/observations/{id}`
**Supprimer une observation**

#### `POST /api/observations/create-follow-up-intervention`
**Créer une intervention de suivi d'observations**
```json
{
  "observation_ids": [1, 2, 3],
  "intitule": "Intervention de suivi",
  "entreprise_nom": "Entreprise ABC",
  "intervenant_nom": "Jean Dupont"
}
```

**Statuts de traitement** : `nouveau`, `en_cours`, `traite`, `reporte`
**Priorités** : `urgent`, `normal`, `faible`

**Format de réponse Observation**
```json
{
  "id": 1,
  "rapport_id": 1,
  "identification": "OBS-001",
  "libelle": "Extincteur manquant",
  "localisation": "Couloir niveau 1",
  "priorite": "urgent",
  "statut_traitement": "nouveau",
  "deja_signalee": false,
  "date_signalement_precedent": null,
  "needs_follow_up": true,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "rapport": {
    "id": 1,
    "titre": "Rapport de contrôle",
    "statut": "final"
  },
  "parties": [...],
  "fichiers": [...],
  "interventions_suivi": [...]
}
```

### 📁 Gestion des fichiers d'observations

#### `POST /api/observations/{id}/files`
**Uploader un fichier**
```json
{
  "file": "fichier_binaire",
  "version": 1
}
```

#### `GET /api/observations/{id}/files/{fichier_id}`
**Télécharger un fichier**

#### `DELETE /api/observations/{id}/files/{fichier_id}`
**Supprimer un fichier**

**Limites** : 5MB par fichier, compression automatique des images

### 📊 Inventaires par partie

#### `GET /api/inventaires-partie`
**Liste des inventaires**
- **Filtres** : `?partie_id=1`, `?product_id=123`

#### `POST /api/inventaires-partie`
**Créer un inventaire**
```json
{
  "partie_id": 1,
  "type_equipement": "extincteur",
  "quantite": 5,
  "description": "Extincteurs CO2 6kg"
}
```

#### `GET /api/inventaires-partie/{id}`
**Détails d'un inventaire**

#### `PUT /api/inventaires-partie/{id}`
**Mettre à jour un inventaire**

#### `DELETE /api/inventaires-partie/{id}`
**Supprimer un inventaire**

#### `GET /api/parties/{id}/inventaires`
**Inventaires d'une partie**

#### `POST /api/inventaires-partie/{id}/sync`
**Synchroniser avec une API externe**

## 🔒 Système de permissions

### Rôles disponibles

1. **super-admin** : Accès total au système
2. **admin** : Administrateur AXIGNIS (accès étendu)
3. **client-admin** : Administrateur client (gestion de son organisation)
4. **user** : Utilisateur standard (lecture/écriture limitée)
5. **viewer** : Lecture seule

### Permissions principales

#### CRUD de base
- `view_sites`, `create_sites`, `edit_sites`, `delete_sites`
- `view_batiments`, `create_batiments`, `edit_batiments`, `delete_batiments`
- `view_niveaux`, `create_niveaux`, `edit_niveaux`, `delete_niveaux`
- `view_parties`, `create_parties`, `edit_parties`, `delete_parties`

#### Permissions métier
- `security_register.*` : Gestion du registre de sécurité
- `equipment_base.*` : Gestion de la base d'équipements
- `sites.manage`, `buildings.manage`, `parts.manage`
- `interventions.manage`, `reports.manage`
- `users.manage`, `clients.manage`

## 🌐 Paramètres de requête utiles

### Paramètres généraux
- `?include_stats=true` : Inclure les statistiques dans la réponse
- `?with=relation1,relation2` : Charger des relations spécifiques

### Filtres disponibles par endpoint

#### Filtres hiérarchiques
- `GET /api/niveaux` : `?batiment_id=1`
- `GET /api/parties` : `?batiment_id=1`, `?niveau_id=1`
- `GET /api/inventaires-partie` : `?partie_id=1`, `?product_id=123`

#### Filtres par statut
- `GET /api/interventions` : `?statut=planifie|en_cours|termine|annule`
- `GET /api/rapports` : `?statut=brouillon|finalise|signe|archive`
- `GET /api/observations` : `?statut_traitement=nouveau|en_cours|traite|reporte`

#### Filtres par relation
- `GET /api/interventions` : `?type_intervention_id=1`, `?partie_id=1`
- `GET /api/rapports` : `?intervention_id=1`, `?type_rapport_id=1`, `?partie_id=1`
- `GET /api/observations` : `?rapport_id=1`, `?partie_id=1`

#### Filtres par priorité
- `GET /api/observations` : `?priorite=urgent|normal|faible`

#### Exemples de filtres combinés
```
GET /api/interventions?statut=en_cours&type_intervention_id=1
GET /api/observations?rapport_id=5&statut_traitement=nouveau&priorite=urgent
GET /api/rapports?intervention_id=2&statut=brouillon&partie_id=3
```

> **Note** : D'autres filtres pourront être ajoutés à l'avenir selon les besoins métier. Cette liste sera mise à jour régulièrement.

## 🔗 Relations entre entités

### Modèle de données relationnel

```
User (Utilisateur)
├── Organisation (isolation par organisation)
├── Roles & Permissions (Spatie)

Site
├── Client (User)
├── Batiments []
├── DroitsSite []

Batiment
├── Site
├── Niveaux []
├── Parties [] (relation directe)
├── DroitsBatiment []
├── Typologies (ERP, IGH, HAB, BUP)

Niveau
├── Batiment
├── Parties [] (relation pivot avec données)
├── DroitsNiveau []

Partie
├── Batiment
├── Niveaux [] (relation pivot avec données)
├── Lots []
├── DroitsPartie []
├── Interventions []
├── Rapports []
├── Observations []
├── Inventaires []

Intervention
├── TypeIntervention
├── Parties []
├── Rapports []
├── ObservationsSuivi []

Rapport
├── Intervention
├── TypeRapport
├── Parties []
├── Observations []

Observation
├── Rapport
├── Parties []
├── Fichiers []
├── InterventionsSuivi []
```

## 📝 Format des réponses d'erreur

### Erreurs de validation (422)
```json
{
  "message": "Les données fournies sont invalides.",
  "errors": {
    "email": [
      "Le format de l'email est invalide."
    ],
    "password": [
      "Le mot de passe doit contenir au moins 8 caractères."
    ]
  }
}
```

### Erreurs d'authentification (401)
```json
{
  "message": "Identifiants incorrects."
}
```

### Erreurs d'autorisation (403)
```json
{
  "message": "Accès refusé. Permissions insuffisantes."
}
```

### Erreurs de ressource non trouvée (404)
```json
{
  "message": "Ressource non trouvée."
}
```

## 🚀 Fonctionnalités avancées

### Compression automatique des fichiers
- Images : Compression JPEG/PNG automatique
- Limite : 5MB par fichier
- Formats supportés : JPEG, PNG, PDF

### Gestion des signatures
- Interventions et rapports signables
- Horodatage automatique
- Traçabilité complète

### Suivi des observations
- Création automatique d'interventions de suivi
- Liaison bidirectionnelle observations ↔ interventions
- Gestion des statuts de traitement

### Isolation par organisation
- Chaque utilisateur voit uniquement les données de son organisation
- Gestion des droits granulaires par entité

## 🔧 En-têtes requis

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## 🌟 Conseils d'utilisation

### Authentification et sécurité
1. **Authentification** : Toujours vérifier la validité du token avant chaque requête
2. **Isolation** : Chaque utilisateur voit uniquement les données de son organisation
3. **Permissions** : Vérifier les permissions utilisateur avant d'afficher les actions

### Optimisation des requêtes
4. **Relations** : Utiliser les paramètres `with` pour optimiser les requêtes
5. **Filtres** : Utiliser les filtres pour limiter les données transmises
6. **Pagination** : Prévoir la pagination pour les listes importantes (non implémentée actuellement)

### Gestion des données
7. **Gestion d'erreurs** : Implémenter une gestion robuste des codes d'erreur
8. **Statistiques** : Utiliser `include_stats=true` pour les tableaux de bord
9. **Fichiers** : Prévoir un indicateur de progression pour les uploads
10. **Statuts** : Respecter les workflows des statuts (brouillon → finalisé → signé → archivé)

### Filtres intelligents
11. **Filtres hiérarchiques** : Utiliser `batiment_id` pour les niveaux, `niveau_id` pour les parties
12. **Filtres combinés** : Combiner plusieurs filtres pour des recherches précises
13. **Filtres métier** : Utiliser les filtres par statut et priorité pour la gestion des workflows

## 📞 Support

Pour toute question technique ou demande d'évolution, contactez l'équipe de développement AXIGNIS.

---

*Document généré le : $(date)*
*Version API : Laravel 11 + Sanctum*
*Dernière mise à jour : $(date)* 