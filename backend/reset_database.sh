#!/bin/bash

echo "🔄 Réinitialisation de la base de données..."
echo ""

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur : Ce script doit être exécuté depuis le répertoire backend/"
    exit 1
fi

# Demander confirmation
echo "⚠️  Cette action va :"
echo "   - Supprimer toutes les données existantes"
echo "   - Recréer les tables"
echo "   - Créer uniquement le super admin et les rôles/permissions"
echo ""
read -p "Continuer ? (y/N) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Opération annulée."
    exit 1
fi

echo "🗑️  Suppression des anciennes données..."
php artisan migrate:fresh --force

echo "🌱 Création des rôles, permissions et super admin..."
php artisan db:seed --force

echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo ""
echo "✅ Base de données réinitialisée avec succès !"
echo ""
echo "🔑 Comptes créés :"
echo "   Super Admin: admin@axignis.com / password123"
echo "   Entreprise 1: michel.leroy@cleanpro.com / password123"
echo "   Entreprise 2: sylvie.dubois@maintenance-plus.fr / password123"
echo "   Intervenant 1: thomas.rousseau@securitech.com / password123"
echo "   Intervenant 2: celine.lambert@controle-securite.fr / password123"
echo ""
echo "ℹ️  Vous pouvez maintenant créer vos sites, bâtiments et autres données via l'interface d'administration." 