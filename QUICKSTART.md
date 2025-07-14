# 🚀 Guide de Démarrage Rapide - Registre de Sécurité

## 📋 Vue d'ensemble

Ce projet est composé de :
- **Backend** : API Laravel (PHP) dans le dossier `backend/`
- **Frontend** : Application React TypeScript dans le dossier `frontend/`

## 🔧 Prérequis

### Backend
- PHP 8.2+
- Composer
- Extension SQLite pour PHP

### Frontend  
- Node.js 20.19.0+
- npm 10.1.0+

## ⚡ Installation Rapide

### 1. Configuration du Backend

```bash
# Aller dans le dossier backend
cd backend

# Installer les dépendances PHP
composer install

# Créer le fichier .env
cp .env.example .env
# OU créer manuellement avec le contenu du guide backend/SETUP_DATABASE.md

# Générer la clé d'application Laravel
php artisan key:generate

# Créer la base de données SQLite
# Windows PowerShell
New-Item -Path "database/database.sqlite" -ItemType File
# Linux/Mac
touch database/database.sqlite

# Exécuter les migrations avec les seeders
php artisan migrate --seed

# Lancer le serveur backend
php artisan serve
```

Le backend sera accessible sur `http://localhost:8000`

### 2. Configuration du Frontend

```bash
# Ouvrir un nouveau terminal et aller dans le dossier frontend
cd frontend

# Installer les dépendances Node.js
npm install

# Créer le fichier .env pour le frontend
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Lancer le serveur frontend
npm run dev
```

Le frontend sera accessible sur `http://localhost:5173`

## 🔐 Première utilisation

### Créer un compte administrateur

1. Aller sur `http://localhost:5173/register`
2. Remplir le formulaire d'inscription :
   - Nom : Admin
   - Prénom : System  
   - Email : admin@registre.com
   - Organisation : Mon Entreprise
   - Mot de passe : password123

### Tester l'application

1. Se connecter avec le compte créé
2. Naviguer dans l'interface :
   - **Dashboard** : Vue d'ensemble
   - **Sites** : Gestion des sites (fonctionnel)
   - **Bâtiments** : À implémenter
   - **Autres modules** : À implémenter

## 📊 Base de données utilisée

- **Type** : SQLite
- **Fichier** : `backend/database/database.sqlite`
- **Tables** : 38 tables créées par les migrations
- **Structure complète** : Voir `backend/SETUP_DATABASE.md`

### Principales tables
- `users` - Utilisateurs et authentification
- `sites` - Sites clients
- `batiments` - Bâtiments avec typologies (ERP, IGH, HAB, BUP)
- `niveaux` - Niveaux des bâtiments
- `parties` - Parties des bâtiments
- `interventions` - Interventions de maintenance
- `rapports` - Rapports de vérification
- `observations` - Observations et non-conformités

## 🌐 Architecture API

### Endpoints d'authentification
- `POST /api/login` - Connexion
- `POST /api/register` - Inscription  
- `GET /api/me` - Informations utilisateur
- `POST /api/logout` - Déconnexion

### Endpoints principaux (authentifiés)
- `GET|POST|PUT|DELETE /api/sites` - Gestion des sites
- `GET|POST|PUT|DELETE /api/batiments` - Gestion des bâtiments
- `GET|POST|PUT|DELETE /api/parties` - Gestion des parties
- `GET|POST|PUT|DELETE /api/interventions` - Gestion des interventions
- `GET|POST|PUT|DELETE /api/rapports` - Gestion des rapports

## 🎨 Technologies utilisées

### Backend
- **Laravel 11** - Framework PHP
- **Sanctum** - Authentification API  
- **Spatie Permission** - Gestion des rôles
- **SQLite** - Base de données

### Frontend
- **React 19** - Bibliothèque UI
- **TypeScript** - Typage statique
- **Vite** - Build tool
- **Tailwind CSS** - Styles
- **React Router** - Routage
- **Axios** - Client HTTP

## ✅ Fonctionnalités implémentées

### ✅ Backend (Laravel)
- [x] Authentification JWT avec Sanctum
- [x] CRUD Sites avec permissions
- [x] CRUD Bâtiments avec typologies
- [x] Models et migrations complets
- [x] API REST complète
- [x] Seeders avec données de test

### ✅ Frontend (React)
- [x] Authentification (login/register/logout)
- [x] Gestion des sites (CRUD complet)
- [x] Dashboard avec statistiques
- [x] Layout responsive
- [x] Gestion d'état avec Context API

### 🔄 En cours de développement
- [ ] Interface pour la gestion des bâtiments
- [ ] Interface pour la gestion des parties
- [ ] Interface pour la gestion des interventions
- [ ] Interface pour la gestion des rapports
- [ ] Génération de PDF

## 🚨 Résolution de problèmes

### Erreur Backend

**"Database does not exist"**
```bash
cd backend
touch database/database.sqlite
php artisan migrate --seed
```

**"Key not generated"**
```bash
php artisan key:generate
```

### Erreur Frontend

**"crypto.hash is not a function"**
- Mettre à jour Node.js vers 20.19.0+

**"Cannot connect to API"**
- Vérifier que le backend est lancé sur http://localhost:8000
- Vérifier le fichier `.env` du frontend

**Erreur CORS**
```bash
# Dans backend/config/cors.php, vérifier :
'allowed_origins' => ['http://localhost:5173'],
```

## 📚 Documentation détaillée

- **Backend** : `backend/SETUP_DATABASE.md`
- **Frontend** : `frontend/README.md` et `frontend/INSTALLATION.md`
- **API** : `backend/README.md`

## 🔮 Prochaines étapes

1. **Implémenter les modules manquants** dans le frontend
2. **Ajouter les tests** (PHPUnit pour le backend, Jest pour le frontend)  
3. **Optimiser les performances**
4. **Ajouter la génération de PDF**
5. **Déploiement en production**

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature
3. Faire les modifications
4. Tester les changements
5. Créer une Pull Request

---

**🎯 Objectif** : Application complète de gestion de registre de sécurité selon les spécifications techniques fournies, avec interface moderne et API robuste. 