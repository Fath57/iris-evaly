# Evaly - Docker Setup

## Prérequis

- Docker Desktop ou Docker Engine
- Docker Compose v2+

## Services

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| **app** | evaly-app | 9000 | PHP 8.3-FPM |
| **nginx** | evaly-nginx | 8007 | Serveur web |
| **mysql** | evaly-mysql | 3307 | Base de données MySQL 8.0 |
| **redis** | evaly-redis | 6380 | Cache et sessions |
| **queue** | evaly-queue | - | Worker pour les jobs |
| **scheduler** | evaly-scheduler | - | Planificateur de tâches |

## Installation rapide

```bash
# 1. Copier le fichier d'environnement
cp .env.docker .env

# 2. Lancer le setup complet
make setup
```

L'application sera accessible sur **http://localhost:8007**

## Installation manuelle

```bash
# 1. Copier la configuration
cp .env.docker .env

# 2. Construire les images
docker compose build

# 3. Démarrer les containers
docker compose up -d

# 4. Générer la clé d'application
docker compose exec app php artisan key:generate

# 5. Exécuter les migrations
docker compose exec app php artisan migrate --seed

# 6. Builder le frontend
docker compose run --rm node sh -c "npm install && npm run build"

# 7. Créer le lien storage
docker compose exec app php artisan storage:link
```

## Commandes Make

```bash
make help          # Afficher l'aide
make build         # Construire les images Docker
make up            # Démarrer les containers
make down          # Arrêter les containers
make restart       # Redémarrer les containers
make logs          # Voir les logs en temps réel
make shell         # Accéder au shell du container app
make mysql         # Accéder à MySQL CLI
make redis         # Accéder à Redis CLI
make fresh         # Migration fresh + seeders
make seed          # Exécuter les seeders
make test          # Lancer les tests
make npm-install   # Installer les dépendances npm
make npm-build     # Builder le frontend
make cache-clear   # Vider tous les caches
make optimize      # Optimiser pour la production
```

## Commandes Docker utiles

```bash
# Voir le statut des containers
docker compose ps

# Voir les logs d'un service
docker compose logs app -f
docker compose logs nginx -f
docker compose logs mysql -f

# Exécuter une commande artisan
docker compose exec app php artisan <commande>

# Exécuter composer
docker compose exec app composer <commande>

# Reconstruire un service spécifique
docker compose build app --no-cache

# Arrêter et supprimer les volumes
docker compose down -v
```

## Configuration

### Variables d'environnement (.env)

Les variables principales pour Docker :

```env
# Base de données
DB_HOST=mysql          # Nom du service Docker
DB_PORT=3306
DB_DATABASE=evaly
DB_USERNAME=evaly
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis       # Nom du service Docker
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_STORE=redis
```

### Ports exposés

| Service | Port interne | Port externe |
|---------|--------------|--------------|
| Nginx | 80 | 8007 |
| MySQL | 3306 | 3307 |
| Redis | 6379 | 6380 |

> Les ports externes peuvent être modifiés dans `docker-compose.yml`

## Structure des fichiers Docker

```
evaly/
├── Dockerfile              # Image PHP 8.3-FPM
├── docker-compose.yml      # Orchestration des services
├── .dockerignore           # Fichiers exclus du build
├── .env.docker             # Template de configuration
├── Makefile                # Commandes simplifiées
└── docker/
    ├── nginx/
    │   └── default.conf    # Configuration Nginx
    └── php/
        └── local.ini       # Configuration PHP
```

## Développement

### Mode développement avec hot reload

Pour le développement frontend avec Vite :

```bash
# Terminal 1 - Démarrer les services
docker compose up -d

# Terminal 2 - Lancer Vite en mode dev
npm run dev
```

Modifier `.env` :
```env
APP_URL=http://localhost:5173
```

### Accès à la base de données

Depuis l'hôte :
```bash
mysql -h 127.0.0.1 -P 3307 -u evaly -psecret evaly
```

Ou via le container :
```bash
make mysql
```

### Debug

```bash
# Voir les logs de tous les services
docker compose logs -f

# Inspecter un container
docker inspect evaly-app

# Voir l'utilisation des ressources
docker stats
```

## Production

### Optimisations recommandées

```bash
# Dans le container
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Ou via Make
make optimize
```

### Variables d'environnement production

```env
APP_ENV=production
APP_DEBUG=false
```

## Dépannage

### Les containers redémarrent en boucle

```bash
# Vérifier les logs
docker compose logs queue --tail 50
docker compose logs scheduler --tail 50

# Redémarrer proprement
docker compose down
docker compose up -d
```

### Erreur de permissions storage

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Erreur de connexion MySQL

Attendre que MySQL soit prêt :
```bash
docker compose exec app php artisan migrate --seed
# Si erreur, attendre 10 secondes et réessayer
```

### Reconstruire depuis zéro

```bash
# Arrêter et supprimer tout
docker compose down -v --rmi all

# Reconstruire
docker compose build --no-cache
docker compose up -d
```

## Volumes persistants

Les données sont stockées dans des volumes Docker :

- `evaly-mysql-data` : Données MySQL
- `evaly-redis-data` : Données Redis

Pour supprimer les volumes :
```bash
docker compose down -v
```
