# 🚀 Installation Rapide - Frontend

## Prérequis
- Node.js 20.19.0 ou supérieur
- NPM 10.1.0 ou supérieur
- API Laravel lancée sur `http://localhost:8000`

## Installation

1. **Installer les dépendances**
```bash
npm install
```

2. **Créer le fichier .env**
```bash
# Créer le fichier .env à la racine du frontend
echo "VITE_API_URL=http://localhost:8000/api" > .env
```

3. **Lancer le serveur de développement**
```bash
npm run dev
```

L'application sera accessible sur `http://localhost:5173`

## Comptes de test

Une fois l'API Laravel configurée, vous pouvez :

1. **Créer un compte** via `/register`
2. **Se connecter** via `/login`

## Fonctionnalités disponibles

- ✅ **Authentification** (Connexion/Inscription)
- ✅ **Gestion des sites** (CRUD complet)
- ✅ **Dashboard** avec statistiques
- 🔄 **Autres modules** (en cours de développement)

## Structure de l'application

```
frontend/
├── src/
│   ├── components/     # Composants réutilisables
│   ├── pages/          # Pages principales
│   ├── services/       # Services API
│   ├── contexts/       # Contextes React
│   ├── types/          # Types TypeScript
│   └── utils/          # Utilitaires
├── public/             # Assets statiques
└── docs/               # Documentation
```

## Aide au développement

- **Linting** : `npm run lint`
- **Build** : `npm run build`
- **Preview** : `npm run preview`

## Problèmes courants

### Erreur crypto.hash
Si vous avez cette erreur, mettez à jour Node.js vers la version 20.19.0+

### Erreur CORS
Vérifiez que l'API Laravel accepte les requêtes depuis `http://localhost:5173`

### Erreur de connexion API
Vérifiez que l'API Laravel est lancée sur `http://localhost:8000` 