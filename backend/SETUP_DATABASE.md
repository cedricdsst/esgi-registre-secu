# 🗄️ Configuration Base de Données - Backend Laravel

## 📋 Situation actuelle

- **Type de BDD** : SQLite (par défaut)
- **Status** : ❌ Non configurée
- **Fichier .env** : ❌ Manquant
- **Base de données** : ❌ Non créée
- **Migrations** : ❌ Non exécutées

## 🚀 Configuration Rapide (SQLite - Recommandé pour le développement)

### 1. Créer le fichier .env

```bash
# Dans le dossier backend/
cp .env.example .env
# OU créer manuellement le fichier .env avec le contenu ci-dessous
```

**Contenu du fichier .env** :
```env
APP_NAME="Registre de Sécurité"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Base de données SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/backend/database/database.sqlite

# Ou pour une base de données relative
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
```

### 2. Générer la clé d'application

```bash
php artisan key:generate
```

### 3. Créer la base de données SQLite

```bash
# Créer le fichier de base de données
touch database/database.sqlite

# Ou sur Windows PowerShell
New-Item -Path "database/database.sqlite" -ItemType File
```

### 4. Exécuter les migrations

```bash
# Exécuter toutes les migrations
php artisan migrate

# Avec les seeders (données de test)
php artisan migrate --seed
```

### 5. Vérifier la configuration

```bash
# Tester la connexion à la base de données
php artisan db:show

# Lister les tables créées
php artisan db:table --database=sqlite
```

## 🐘 Configuration MySQL/MariaDB (Production)

Si vous préférez MySQL/MariaDB :

### 1. Modifier le .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=registre_securite
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Créer la base de données
```sql
CREATE DATABASE registre_securite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Exécuter les migrations
```bash
php artisan migrate --seed
```

## 📊 Structure de la base de données

Le système comprend **38 migrations** qui créent les tables suivantes :

### 👥 Authentification & Permissions
- `users` - Utilisateurs
- `personal_access_tokens` - Tokens API
- `permissions` - Système de permissions Spatie
- `roles` - Rôles utilisateurs

### 🏢 Sites & Bâtiments
- `sites` - Sites clients
- `batiments` - Bâtiments des sites
- `niveaux` - Niveaux des bâtiments
- `parties` - Parties des bâtiments
- `lots` - Lots pour les parties

### 🔐 Droits d'accès
- `droits_site` - Droits par site
- `droits_batiment` - Droits par bâtiment
- `droits_niveau` - Droits par niveau
- `droits_partie` - Droits par partie

### 🏗️ Typologies de bâtiments
- `erp_types` - Types ERP
- `erp_categories` - Catégories ERP
- `erp` - Établissements ERP
- `igh_classes` - Classes IGH
- `igh` - Immeubles IGH
- `hab_familles` - Familles HAB
- `hab` - Bâtiments HAB
- `bup` - Bâtiments BUP

### 🏢 Entreprises & Interventions
- `entreprises` - Entreprises intervenantes
- `types_interventions` - Types d'interventions
- `interventions` - Interventions
- `types_rapports` - Types de rapports
- `rapports` - Rapports de vérification
- `observations` - Observations des rapports
- `fichiers_rapports` - Fichiers joints
- `inventaires_partie` - Inventaires par partie

### 🔗 Tables de liaison
- `intervention_partie` - Interventions ↔ Parties
- `rapport_partie` - Rapports ↔ Parties
- `observation_partie` - Observations ↔ Parties
- `partie_lot` - Parties ↔ Lots
- `partie_niveau` - Parties ↔ Niveaux
- `intervention_suivi_observation` - Suivi d'observations

## ✅ Vérification de la configuration

### Commandes utiles
```bash
# Statut des migrations
php artisan migrate:status

# Informations sur la base de données
php artisan db:show

# Lister les seeders disponibles
php artisan db:seed --class=DatabaseSeeder --dry-run

# Reset complet de la base de données
php artisan migrate:fresh --seed
```

### Tests de fonctionnement
```bash
# Tester les routes API
php artisan route:list

# Lancer le serveur de développement
php artisan serve

# Tester l'authentification
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"nom":"Test","prenom":"User","email":"test@test.com","password":"password","password_confirmation":"password","organisation":"Test Org"}'
```

## 🔧 Configuration CORS pour le frontend

Dans `config/cors.php`, assurez-vous d'avoir :
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:5173'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

## 🚨 Problèmes courants

### Erreur "Database does not exist"
```bash
# Vérifier que le fichier SQLite existe
ls -la database/database.sqlite

# Le créer s'il n'existe pas
touch database/database.sqlite
```

### Erreur de permissions
```bash
# Donner les bonnes permissions (Linux/Mac)
chmod 664 database/database.sqlite
chmod 775 database/

# Ou ajuster le propriétaire
sudo chown www-data:www-data database/database.sqlite
```

### Erreur "could not find driver"
```bash
# Vérifier que SQLite est installé
php -m | grep sqlite

# Installer SQLite sur Ubuntu/Debian
sudo apt-get install php-sqlite3

# Sur macOS avec Homebrew
brew install php
```

## 📚 Ressources

- [Documentation Laravel Database](https://laravel.com/docs/database)
- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Spatie Permission Package](https://spatie.be/docs/laravel-permission/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) 