#!/bin/bash

set -e

echo "🚀 Initialisation de l'application Licensing System..."

# Copier le fichier .env.docker vers .env si .env n'existe pas
if [ ! -f .env ]; then
    echo "📝 Copie du fichier .env.docker vers .env..."
    cp .env.docker .env
fi

# Attendre que MySQL soit prêt
echo "⏳ Attente de la disponibilité de MySQL..."
until php artisan db:show 2>/dev/null; do
    echo "MySQL n'est pas encore prêt - attente..."
    sleep 2
done

echo "✅ MySQL est prêt!"

# Créer les répertoires de cache et de logs s'ils n'existent pas
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Définir les permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Vérifier si la base de données est vide
TABLE_COUNT=$(php artisan db:table --count 2>/dev/null | grep -c "." || echo "0")

if [ "$TABLE_COUNT" -eq "0" ]; then
    echo "📦 Base de données vide - Exécution des migrations..."
    php artisan migrate --force

    echo "🌱 Exécution des seeders..."
    php artisan db:seed --force

    echo "✅ Base de données initialisée avec succès!"
else
    echo "ℹ️  Base de données déjà initialisée - Migration..."
    php artisan migrate --force
fi

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimiser l'application
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✨ Initialisation terminée avec succès!"
echo ""
echo "📌 Informations importantes :"
echo "   - Application accessible sur : http://localhost:8000"
echo "   - Base de données MySQL : licensing"
echo ""
echo "🔑 Identifiants de test :"
echo "   Admin  : admin@example.com / admin"
echo "   Client : client@example.com / client"
echo ""
