# Frontend - Registre de Sécurité

Application React TypeScript pour la gestion du registre de sécurité, développée avec Vite et Tailwind CSS.

## 🚀 Technologies utilisées

- **React 19** - Bibliothèque UI
- **TypeScript** - Typage statique
- **Vite** - Bundler et serveur de développement
- **Tailwind CSS** - Framework CSS
- **React Router** - Routage côté client
- **Axios** - Client HTTP
- **React Hook Form** - Gestion des formulaires
- **Lucide React** - Icônes
- **Zod** - Validation des schémas

## 📦 Installation

### Prérequis

- Node.js 20.19.0 ou supérieur
- npm 10.1.0 ou supérieur

### Installation des dépendances

```bash
npm install
```

## 🔧 Configuration

### Variables d'environnement

Créez un fichier `.env` à la racine du projet avec les variables suivantes :

```env
VITE_API_URL=http://localhost:8000/api
```

### Configuration de l'API

L'application est configurée pour communiquer avec une API Laravel sur `http://localhost:8000/api`.

Assurez-vous que :
1. L'API Laravel est lancée sur le port 8000
2. CORS est configuré pour accepter les requêtes depuis `http://localhost:5173`

## 🚀 Commandes disponibles

### Développement

```bash
# Lancer le serveur de développement
npm run dev

# L'application sera disponible sur http://localhost:5173
```

### Build

```bash
# Construire l'application pour la production
npm run build

# Prévisualiser le build de production
npm run preview
```

### Linting

```bash
# Analyser le code avec ESLint
npm run lint
```

## 🔐 Authentification

L'application utilise un système d'authentification avec JWT :

- **Connexion** : `/login`
- **Inscription** : `/register`
- **Gestion des tokens** : Stockage en localStorage
- **Protection des routes** : Middleware de vérification

## 🏗️ Structure du projet

```
src/
├── components/          # Composants réutilisables
│   ├── auth/           # Composants d'authentification
│   ├── common/         # Composants communs (Modal, LoadingSpinner, etc.)
│   ├── layout/         # Composants de layout (Header, Sidebar, etc.)
│   └── sites/          # Composants spécifiques aux sites
├── contexts/           # Contextes React (AuthContext)
├── pages/              # Pages de l'application
├── services/           # Services API
├── types/              # Types TypeScript
├── utils/              # Utilitaires
├── App.tsx             # Composant racine
├── main.tsx            # Point d'entrée
└── index.css           # Styles globaux
```

## 🎨 Système de design

### Couleurs

- **Primary** : Bleu (couleurs 50-900)
- **Secondary** : Gris (couleurs 50-900)
- **Accent** : Rouge pour les actions destructives

### Composants

Les composants utilisent des classes CSS utilitaires définies dans `index.css` :

- `.btn` : Boutons de base
- `.btn-primary` : Bouton principal
- `.btn-secondary` : Bouton secondaire
- `.form-input` : Champs de formulaire
- `.card` : Cartes de contenu
- `.sidebar` : Barre latérale

## 📱 Fonctionnalités implémentées

### ✅ Authentification
- [x] Connexion utilisateur
- [x] Inscription utilisateur
- [x] Déconnexion
- [x] Protection des routes
- [x] Gestion des tokens JWT

### ✅ Gestion des sites
- [x] Liste des sites
- [x] Création de sites
- [x] Modification de sites
- [x] Suppression de sites
- [x] Modal de formulaire

### ✅ Interface utilisateur
- [x] Layout responsive
- [x] Navigation sidebar
- [x] Header avec informations utilisateur
- [x] Dashboard avec statistiques
- [x] Composants réutilisables (Modal, LoadingSpinner)

### 🔄 En cours de développement
- [ ] Gestion des bâtiments
- [ ] Gestion des niveaux
- [ ] Gestion des parties
- [ ] Gestion des entreprises
- [ ] Gestion des interventions
- [ ] Gestion des rapports
- [ ] Gestion des observations
- [ ] Génération de PDF

## 🐛 Dépannage

### Problème avec Vite et Node.js

Si vous rencontrez l'erreur `crypto.hash is not a function`, c'est dû à une incompatibilité entre la version de Vite et Node.js.

**Solution** : Mettre à jour Node.js vers la version 20.19.0 ou supérieure.

### Problème de CORS

Si vous avez des erreurs CORS, assurez-vous que l'API Laravel est configurée pour accepter les requêtes depuis `http://localhost:5173`.

### Problème de connexion à l'API

1. Vérifiez que l'API Laravel est lancée sur `http://localhost:8000`
2. Vérifiez la configuration de l'URL API dans `src/utils/api.ts`
3. Vérifiez que les endpoints existent dans l'API

## 🔗 API Endpoints utilisés

### Authentification
- `POST /login` - Connexion
- `POST /register` - Inscription
- `GET /me` - Informations utilisateur
- `POST /logout` - Déconnexion

### Sites
- `GET /sites` - Liste des sites
- `POST /sites` - Créer un site
- `GET /sites/{id}` - Détails d'un site
- `PUT /sites/{id}` - Modifier un site
- `DELETE /sites/{id}` - Supprimer un site

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📝 Notes de développement

- L'application utilise TypeScript strict
- Les composants sont typés avec des interfaces
- L'état global est géré avec React Context
- Les appels API sont centralisés dans des services
- La validation côté client utilise les types TypeScript

## 🔮 Roadmap

- [ ] Implémentation complète de tous les modules
- [ ] Tests unitaires et d'intégration
- [ ] Storybook pour la documentation des composants
- [ ] Internationalisation (i18n)
- [ ] Mode sombre
- [ ] PWA (Progressive Web App)
- [ ] Optimisation des performances
