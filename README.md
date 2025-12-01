# Licensing System - Système de Gestion de Licences

## Description

Application complète de gestion de licences développée avec Laravel 12, Vue.js 3, Inertia.js et TypeScript. Le système permet de gérer des licences logicielles, des applications et des utilisateurs avec différents niveaux d'accès.

## Fonctionnalités principales

- 🔐 **Authentification sécurisée** avec Laravel Fortify (2FA disponible)
- 👥 **Gestion multi-utilisateurs** (Admin et Client)
- 🎫 **Gestion de licences** (création, modification, activation/suspension)
- 📱 **Gestion d'applications** liées aux licences
- 📊 **Tableau de bord interactif** avec statistiques en temps réel
- 🎨 **Interface moderne** avec Tailwind CSS et mode sombre
- 🌐 **Interface multilingue** (français)

## Prérequis

- Docker
- Docker Compose

## Installation et démarrage

### 🚀 Démarrage rapide

Pour démarrer l'application, exécutez simplement :

```bash
docker compose up
```

Cette commande unique :
- ✅ Construit les images Docker nécessaires
- ✅ Démarre MySQL et configure la base de données
- ✅ Installe toutes les dépendances (Composer et NPM)
- ✅ Exécute les migrations de base de données
- ✅ Seed la base de données avec des données de test
- ✅ Build les assets frontend
- ✅ Démarre l'application Laravel

### 📌 Première utilisation

Lors du premier démarrage, l'initialisation peut prendre quelques minutes (installation des dépendances, build des assets, etc.). Les démarrages suivants seront beaucoup plus rapides.

Attendez le message suivant dans les logs :

```
✨ Initialisation terminée avec succès!

📌 Informations importantes :
   - Application accessible sur : http://localhost:8000
   - Base de données MySQL : licensing

🔑 Identifiants de test :
   Admin  : admin@example.com / admin
   Client : client@example.com / client
```

### 🌐 Accès à l'application

Une fois démarré, l'application est accessible sur :

**http://localhost:8000**

### 🔑 Comptes de test

Deux utilisateurs sont automatiquement créés :

#### Administrateur
- **Email** : `admin@example.com`
- **Mot de passe** : `admin`
- **Permissions** : Accès complet au système, gestion des licences

#### Client
- **Email** : `client@example.com`
- **Mot de passe** : `client`
- **Permissions** : Utilisation des applications et consultation

## Architecture

### Services Docker

L'application utilise 3 services principaux :

1. **mysql** : Base de données MySQL 8.0
2. **app** : Application Laravel avec PHP 8.2
3. **vite** : Serveur de développement Vite pour le frontend

### Stack technique

#### Backend
- **Framework** : Laravel 12
- **Langage** : PHP 8.2
- **Base de données** : MySQL 8.0
- **Authentification** : Laravel Fortify
- **API** : Inertia.js

#### Frontend
- **Framework** : Vue.js 3 avec Composition API
- **Langage** : TypeScript
- **Build tool** : Vite
- **Styling** : Tailwind CSS
- **UI Components** : Composants personnalisés avec shadcn/ui

## Gestion des conteneurs

### Arrêter l'application

```bash
docker compose down
```

### Arrêter et supprimer les volumes (reset complet)

```bash
docker compose down -v
```

### Voir les logs

```bash
docker compose logs -f
```

### Voir les logs d'un service spécifique

```bash
docker compose logs -f app
docker compose logs -f mysql
docker compose logs -f vite
```

### Reconstruire les images

```bash
docker compose build --no-cache
docker compose up
```

### Accéder au conteneur de l'application

```bash
docker compose exec app bash
```

## Structure du projet

```
.
├── app/                    # Code source Laravel
│   ├── Core/              # Classes métier principales
│   ├── Http/              # Controllers et Middleware
│   └── Models/            # Modèles Eloquent
├── database/
│   ├── migrations/        # Migrations de base de données
│   └── seeders/           # Seeders pour les données de test
├── resources/
│   ├── js/                # Code source Vue.js/TypeScript
│   │   ├── components/   # Composants Vue réutilisables
│   │   ├── layouts/      # Layouts de l'application
│   │   └── pages/        # Pages Inertia.js
│   └── views/            # Templates Blade
├── routes/               # Définition des routes
├── docker-compose.yml    # Configuration Docker Compose
├── Dockerfile            # Image Docker de l'application
└── init.sh              # Script d'initialisation automatique
```

## Fonctionnalités détaillées

### Gestion des licences

- Création de licences prédéfinies ou personnalisées
- Définition des limites (nombre d'applications, exécutions/24h)
- Activation/suspension des licences
- Dates de validité configurables
- Attribution aux utilisateurs

### Gestion des applications

- Enregistrement d'applications liées à une licence
- Suivi des exécutions en temps réel
- Limite d'exécutions par jour
- Désactivation/suppression d'applications
- Génération de tokens ULD uniques

### Tableau de bord

- Statistiques en temps réel
- Suivi de l'utilisation des ressources
- Alertes pour les limites atteintes
- Upgrade de licence en un clic
- Visualisation des applications actives

### Authentification

- Connexion sécurisée avec Laravel Fortify
- Authentification à deux facteurs (2FA) optionnelle
- Vérification d'email
- Réinitialisation de mot de passe
- Gestion de session

## Maintenance

### Exécuter des commandes Artisan

```bash
docker compose exec app php artisan <commande>
```

Exemples :
```bash
# Lister les routes
docker compose exec app php artisan route:list

# Créer une migration
docker compose exec app php artisan make:migration create_table_name

# Exécuter les migrations
docker compose exec app php artisan migrate

# Re-seeder la base de données
docker compose exec app php artisan db:seed
```

### Accéder à MySQL

```bash
docker compose exec mysql mysql -u licensing_user -plicensing_password licensing
```

## Dépannage

### L'application ne démarre pas

1. Vérifiez que les ports 8000, 3306 et 5173 sont disponibles
2. Vérifiez les logs : `docker compose logs -f`
3. Essayez de reconstruire : `docker compose build --no-cache`

### Erreur de connexion à la base de données

Attendez quelques secondes que MySQL soit complètement initialisé. Le healthcheck garantit que l'application attend MySQL.

### Les assets frontend ne se chargent pas

Le service Vite peut prendre quelques secondes à démarrer. Rafraîchissez la page après quelques instants.

### Reset complet

```bash
docker compose down -v
docker compose build --no-cache
docker compose up
```

## Contribution

Ce projet a été développé dans le cadre d'un examen technique.

## Licence

Ce projet est sous licence MIT.

## Support

Pour toute question ou problème, veuillez consulter les logs avec `docker compose logs -f`.

---

**Développé avec ❤️ en utilisant Laravel, Vue.js et Docker**
