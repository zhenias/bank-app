# 🐳 Bank App - Docker Setup - Complete Guide

## 📋 Overview

Kompletna konfiguracja Docker dla całego projektu Bank App obejmująca:
- ✅ **Backend**: Laravel API z PHP-FPM + Nginx (port 8000)
- ✅ **Frontend**: React z Vite (port 5174)
- ✅ **Database**: MariaDB 11.4 (port 3306)
- ✅ **Cache**: Redis 7 Alpine (port 6379)
- ✅ **Hot Reload**: Automatyczny reload frontendu w dev mode

## 🎯 Stworzono pliki:

### Główny folder (`/`)
- **docker-compose.yml** - Główna konfiguracja dla całej aplikacji
- **Makefile** - Skróty do najczęstszych komend
- **DOCKER_SETUP.md** - Szczegółowa dokumentacja
- **QUICKSTART.md** - Szybkie instrukcje uruchomienia
- **.dockerignore** - Excludes dla Docker builds

### Backend (`/backend`)
- **Dockerfile** - Multi-stage image z PHP 8.3, FPM, Nginx, Node.js
- **docker-compose.yml** - Standalone compose dla backendu
- **.env.docker** - Domyślne zmienne środowiskowe
- **.env** - Aktualizowane na zmienne Docker
- **.dockerignore** - Backend excludes
- **docker/entrypoint.sh** - Startup script (composer, npm, migracje)
- **docker/nginx.conf** - Konfiguracja Nginx
- **docker/php-fpm.conf** - Konfiguracja PHP-FPM
- **docker/supervisord.conf** - Supervisor (PHP-FPM, Nginx, Queue Worker)
- **docker/scripts/** - Utility skrypty:
  - `start.sh` - Start containers
  - `stop.sh` - Stop containers
  - `migrate.sh` - Run migrations
  - `db-fresh.sh` - Fresh migrations
  - `artisan.sh` - Execute Artisan commands
  - `composer.sh` - Execute Composer commands
  - `shell.sh` - Open bash shell

### Frontend (`/frontend`)
- **Dockerfile** - Node.js 20 Alpine image
- **docker-compose.yml** - Standalone compose dla frontendu
- **.env** - Zmienne dla frontendu (API URL)
- **.env.example** - Template zmiennych
- **.dockerignore** - Frontend excludes
- **vite.config.ts** - Zaktualizowana konfiguracja (HMR, polling watch)
- **docker/scripts/** - Utility skrypty:
  - `start.sh` - Start container
  - `stop.sh` - Stop container
  - `npm.sh` - Execute NPM commands
  - `shell.sh` - Open sh shell

### Wspólne (`/docker/scripts`)
- **start.sh** - Start all services
- **stop.sh** - Stop all services
- **logs.sh** - View logs
- **shell.sh** - Open shell w wybranym kontenerze
- **db-reset.sh** - Reset bazy danych
- **check-ports.sh** - Sprawdzenie dostępności portów
- **status.sh** - Status wszystkich kontenerów

## 🚀 Quick Start

### 1. Wymagania
```bash
# Sprawdzenie dostępności portów
bash docker/scripts/check-ports.sh
```

### 2. Uruchomienie
```bash
# Z głównego folderu
bash docker/scripts/start.sh

# lub
make start
```

### 3. Czekanie na inicjalizację
Pierwsze uruchomienie (~2-5 min):
- ✅ Composer install
- ✅ NPM install
- ✅ Database migrations
- ✅ Configuration caching

```bash
# Monitorowanie logów
bash docker/scripts/logs.sh
# lub
make logs
```

### 4. Otwarcie aplikacji
| Serwis | URL |
|--------|-----|
| 🎨 Frontend | http://localhost:5174 |
| 🔌 Backend API | http://localhost:8000 |
| 📊 MariaDB | localhost:3306 |
| 🚀 Redis | localhost:6379 |

## 📝 Dostępne Komendy

### Makefile (rekomendowane)

```bash
make help              # List wszystkich komend

# Główne
make start             # Start wszystkich serwisów
make stop              # Stop wszystkich serwisów
make restart           # Restart wszystkich serwisów
make logs              # View logs
make build             # Build images

# Backend
make backend-shell     # Shell w backend kontenerze
make migrate           # Run migrations
make fresh             # Fresh migrations (reset DB)
make tinker            # Open Laravel tinker
make artisan CMD="..."  # Run any artisan command
make composer CMD="..." # Run any composer command

# Frontend
make frontend-shell    # Shell w frontend kontenerze
make npm CMD="..."     # Run any npm command

# Database
make db-shell          # Connect to MySQL/MariaDB

# Cleanup
make clean             # Remove all containers/volumes/images
```

### Bash Skrypty

#### Z głównego folderu
```bash
# Start/Stop
bash docker/scripts/start.sh
bash docker/scripts/stop.sh

# Utility
bash docker/scripts/logs.sh
bash docker/scripts/status.sh
bash docker/scripts/check-ports.sh
bash docker/scripts/db-reset.sh
bash docker/scripts/shell.sh <service>
```

#### Z folderu backend/
```bash
cd backend

bash docker/scripts/start.sh
bash docker/scripts/stop.sh
bash docker/scripts/migrate.sh
bash docker/scripts/db-fresh.sh
bash docker/scripts/artisan.sh <command>
bash docker/scripts/composer.sh <command>
bash docker/scripts/shell.sh
```

#### Z folderu frontend/
```bash
cd frontend

bash docker/scripts/start.sh
bash docker/scripts/stop.sh
bash docker/scripts/npm.sh <command>
bash docker/scripts/shell.sh
```

## 🔧 Zmienne Środowiskowe

### Backend (.env)

```bash
# Database
DB_CONNECTION=mariadb
DB_HOST=mariadb              # lub 127.0.0.1 (localhost)
DB_PORT=3306
DB_DATABASE=backend
DB_USERNAME=laravel
DB_PASSWORD=laravel_password

# Redis
REDIS_HOST=redis            # lub 127.0.0.1 (localhost)
REDIS_PORT=6379
CACHE_DRIVER=redis
CACHE_STORE=redis

# Session
SESSION_DRIVER=cookie

# Queue
QUEUE_CONNECTION=database
```

### Frontend (.env)

```bash
VITE_API_URL=http://localhost:8000/api
NODE_ENV=development
```

## 🔍 Troubleshooting

### Kontener nie startuje
```bash
# Sprawdź logi
bash docker/scripts/logs.sh

# Rebuild image
docker-compose build --no-cache

# Clear i restart
docker-compose down -v
docker-compose up -d
```

### Baza danych nie inicjalizuje się
```bash
# Czekaj http://localhost:8000 (backend kontener uruchomi migracje)
# Lub ręcznie:
make migrate
```

### HMR (Hot Module Replacement) nie działa
- ✅ Już skonfigurowany w vite.config.ts frontendu
- ✅ usePolling: true dla better Docker compatibility
- Sprawdź logi (make logs) czy brak błędów WebSocket

### Port już w użyciu
```bash
# Sprawdź jaki proces zajmuje port
lsof -i :8000
lsof -i :5174
lsof -i :3306
lsof -i :6379

# Zmień port w docker-compose.yml jeśli potrzeba
# Lub zatrzymaj zajmujący proces
```

### Connrefused na localhost
- W Dockerze localhost != 127.0.0.1 dla komunikacji
- Użyj nazw serwisów: `mariadb`, `redis`
- .env już ma prawidłowe wartości

## 📊 Struktura Volumes

```yaml
Volumes:
  mariadb_data:     # Database persistence
  redis_data:       # Cache persistence

Volume Mounts (Code Sync):
  ./backend:/app                # Backend kod z localnego folderu
  ./frontend:/app               # Frontend kod z lokalnego folderu
  /app/vendor                   # Composer dependencies (isolated)
  /app/node_modules             # NPM dependencies (isolated)
```

### Kod Synchronizacja
✅ **Zmiany w lokalnym kodzie = automatycznie widoczne w kontenerze**

Nie trzeba restartować:
- Backend: hot reload cache (php artisan config:cache)
- Frontend: Vite hot module replacement (HMR)

## 🐛 Debugging

### Logs
```bash
# Wszystkie kontenery
make logs

# Konkretny kontener
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f mariadb
docker-compose logs -f redis
```

### Shell w kontenerze
```bash
# Backend (bash)
make backend-shell
# lub
bash backend/docker/scripts/shell.sh

# Frontend (sh)
make frontend-shell

# MariaDB (mysql cli)
docker-compose exec mariadb mysql -u laravel -plaravel_password backend

# Redis
docker-compose exec redis redis-cli
```

### Database Commands
```bash
# Laravel Tinker (interactive shell)
make tinker

# Any Artisan command
make artisan CMD="your-command"
make artisan CMD="make:model Post"
make artisan CMD="db:seed"
```

### Supervisor (Backend Process Manager)
```bash
# Check supervisor processes
docker-compose exec backend supervisorctl status

# Restart specific process
docker-compose exec backend supervisorctl restart php-fpm
```

## 🧹 Czyszczenie

### Stop kontenery (zachowaj dane)
```bash
make stop
# lub
bash docker/scripts/stop.sh
```

### Remove kontenery (zachowaj volumes)
```bash
docker-compose down
```

### Remove wszystko (usunie dane!)
```bash
make clean
# lub
docker-compose down -v --rmi all
```

## 📚 Dokumentacja

Więcej informacji w:
- **QUICKSTART.md** - Szybki start w 5 minut
- **DOCKER_SETUP.md** - Szczegółowa dokumentacja

## 🔐 Bezpieczeństwo

### Development
- ✅ Hasła to default dev passwords (zmień w production!)
- ✅ APP_DEBUG=true (zmień na false w production)
- ✅ localhost binding (bezpieczne, bez dostępu netowskiego)

### Production
- ⚠️ Change DB_PASSWORD
- ⚠️ Change APP_KEY (już wygenerowany)
- ⚠️ Set APP_DEBUG=false
- ⚠️ Use environment variables (nie hardcode)
- ⚠️ Setup proper reverse proxy (Nginx/Traefik)
- ⚠️ SSL/TLS certificates

## 🤝 Sieciowanie Docker

Kontenery komunikują się przez Docker network (`bank-app-network`):

```
Frontend (5174) 
    ↓
Backend API (8000)
    ↓
MariaDB (3306) + Redis (6379)
```

Adresy:
- Wewnątrz Docker: `mariadb`, `redis` (Docker DNS)
- Z hostu: `localhost:3306`, `localhost:6379`
- .env: Używamy nazw serwisów (`mariadb`, `redis`)

## 📞 Support

Jeśli masz problemy:

1. **Sprawdź logi**: `make logs`
2. **Sprawdź status**: `bash docker/scripts/status.sh`
3. **Rebuild**: `docker-compose up -d --build`
4. **Reset**: `docker-compose down -v && docker-compose up -d`

## ✅ Checklist первого uruchomienia

- [ ] Zainstalowany Docker Desktop / Docker Engine
- [ ] Porty 8000, 5174, 3306, 6379 dostępne
- [ ] `cd /Users/zhenias/Documents/projects/bank-app`
- [ ] `bash docker/scripts/start.sh` (lub `make start`)
- [ ] Czekaj aż frontend/backend staną się dostępne (~2-5 min)
- [ ] Sprawdź: http://localhost:5174 i http://localhost:8000
- [ ] Zmień hasła i konfigurację przed production

---

**Created:** May 5, 2026  
**Version:** 1.0  
**Author:** GitHub Copilot

