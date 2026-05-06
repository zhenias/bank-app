# Bank App - Docker Setup Guide

Kompletna konfiguracja Docker Compose dla całej aplikacji, z backendem Laravel, frontendem React, MariaDB i Redis.

## 📋 Wymagania

- Docker Desktop (Mac/Windows) lub Docker Engine (Linux)
- Docker Compose v2+
- Port 8000, 5174, 3306, 6379 dostępne

## 🚀 Quick Start

### 1. Przygotowanie

```bash
# Clone repo (jeśli jeszcze nie masz)
cd /Users/zhenias/Documents/projects/bank-app

# Copy .env file
cp backend/.env.docker backend/.env
```

### 2. Uruchomienie wszystkich serwisów

```bash
# Z głównego folderu
bash docker/scripts/start.sh
```

To uruchomi:
- ✅ MariaDB (port 3306)
- ✅ Redis (port 6379)
- ✅ Backend API (port 8000)
- ✅ Frontend (port 5174)

### 3. Sprawdzenie statusu

```bash
# Wyświetlanie logów
bash docker/scripts/logs.sh

# Sprawdzenie kontenerów
docker-compose ps
```

## 🔗 Adresy URL

| Serwis | URL | Port |
|--------|-----|------|
| Backend API | http://localhost:8000 | 8000 |
| Frontend | http://localhost:5174 | 5174 |
| MariaDB | localhost | 3306 |
| Redis | localhost | 6379 |

## 📁 Struktura

```
bank-app/
├── docker-compose.yml              # Main Docker Compose
├── docker/
│   └── scripts/
│       ├── start.sh                # Start all services
│       ├── stop.sh                 # Stop all services
│       ├── logs.sh                 # View logs
│       ├── shell.sh                # Open shell
│       ├── db-reset.sh             # Reset database
│       └── mariadb/
├── backend/
│   ├── docker-compose.yml          # Backend compose (standalone)
│   ├── Dockerfile                  # Backend image
│   ├── .env.docker                 # Docker environment variables
│   ├── .dockerignore
│   └── docker/
│       ├── apache.conf             # Apache configuration
│       └── scripts/
│           ├── start.sh
│           ├── stop.sh
│           ├── migrate.sh
│           ├── db-fresh.sh
│           ├── artisan.sh
│           ├── composer.sh
│           └── shell.sh
└── frontend/
    ├── docker-compose.yml          # Frontend compose (standalone)
    ├── Dockerfile                  # Frontend image
    ├── .dockerignore
    └── docker/
        └── scripts/
            ├── start.sh
            ├── stop.sh
            ├── npm.sh
            └── shell.sh
```

## 🛠️ Dostępne Komendy

### Główne komendy (z głównego folderu)

```bash
# Start
bash docker/scripts/start.sh

# Stop
bash docker/scripts/stop.sh

# Logs
bash docker/scripts/logs.sh

# Shell w wybranym kontenerze
bash docker/scripts/shell.sh backend  # lub mariadb, redis, frontend
bash docker/scripts/shell.sh mariadb
bash docker/scripts/shell.sh redis

# Reset bazy danych
bash docker/scripts/db-reset.sh
```

### Komendy Backend (z folderu backend)

```bash
# Start
bash docker/scripts/start.sh

# Stop
bash docker/scripts/stop.sh

# Run migrations
bash docker/scripts/migrate.sh

# Fresh migration (reset bazy)
bash docker/scripts/db-fresh.sh

# Artisan commands
bash docker/scripts/artisan.sh migrate
bash docker/scripts/artisan.sh tinker
bash docker/scripts/artisan.sh make:model Post

# Composer commands
bash docker/scripts/composer.sh install
bash docker/scripts/composer.sh require laravel/debugbar

# Shell
bash docker/scripts/shell.sh
```

### Komendy Frontend (z folderu frontend)

```bash
# Start
bash docker/scripts/start.sh

# Stop
bash docker/scripts/stop.sh

# NPM commands
bash docker/scripts/npm.sh install
bash docker/scripts/npm.sh run build

# Shell
bash docker/scripts/shell.sh
```

## 📊 Zmienne Środowiskowe

### Backend (.env.docker)

```bash
DB_HOST=mariadb                 # MariaDB container name
DB_PORT=3306
DB_DATABASE=backend
DB_USERNAME=laravel
DB_PASSWORD=laravel_password

REDIS_HOST=redis               # Redis container name
REDIS_PORT=6379
CACHE_DRIVER=redis
```

### Frontend

```bash
VITE_API_URL=http://localhost:8000/api
NODE_ENV=development
```

## 🔍 Debugging

### Podejrzenie logów kontenera

```bash
# Wszystkie kontenery
docker-compose logs -f

# Konkretny kontener
docker-compose logs -f backend
docker-compose logs -f mariadb
docker-compose logs -f redis
docker-compose logs -f frontend
```

### Połączenie do MariaDB z CLI

```bash
docker-compose exec mariadb mysql -u laravel -plaravel_password backend
```

### Połączenie do Redis

```bash
docker-compose exec redis redis-cli
```

## 🧹 Czyszczenie

```bash
# Stop kontenery
bash docker/scripts/stop.sh

# Usuń kontenery
docker-compose down

# Usuń volumes (UWAGA: usunie dane bazy danych!)
docker-compose down -v

# Usuń wszystkie Docker images
docker-compose down --rmi all
```

## 📝 Notatki

- Kod aplikacji jest zamontowany z lokalnego folderu (volume mounts)
- Zmiany w kodzie są automatycznie widoczne w kontenerze bez restartu
- MariaDB dane są przechowywane w `mariadb_data` volume
- Redis dane w `redis_data` volume
- Backend uses Apache + PHP-FPM
- Frontend uses Node.js + Vite dev server

## 🐛 Troubleshooting

### Port już w użyciu

```bash
# Zmień porty w docker-compose.yml
# lub zatrzymaj aplikacje używające te porty

# Sprawdzenie portów
lsof -i :8000
lsof -i :5174
lsof -i :3306
lsof -i :6379
```

### Baza danych nie inicjalizuje się

```bash
# Reset bazy
docker-compose down -v
docker-compose up -d mariadb
# czekaj aż kontener będzie healthy
```

### Node modules / Vendor problemy

```bash
# Rebuild containers
docker-compose up -d --build

# lub wyczyść i zainstaluj od nowa
docker-compose down
docker-compose up -d
```

## 📞 Support

Jeśli masz problemy, sprawdź:
1. Logi: `docker-compose logs -f`
2. Status kontenerów: `docker-compose ps`
3. Czy porty są dostępne: `lsof -i` (Mac/Linux)

---

**Created:** May 5, 2026
**Version:** 1.0

