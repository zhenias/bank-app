# 🚀 Quick Start - Bank App Docker

## 5-minutowy setup dla całej aplikacji

### 1️⃣ Wymagania
- Docker Desktop zainstalowany
- Porty dostępne: 8000, 5174, 3306, 6379

### 2️⃣ Uruchomienie (z głównego folderu)

```bash
# Przejdź do głównego folderu projektu
cd /Users/zhenias/Documents/projects/bank-app

# Start
bash docker/scripts/start.sh
```

### 3️⃣ Czekaj, aż kontenery się uruchomią

Pierwsze uruchomienie może trwać 2-5 minut (instalacja zależności, migracje BD).

```bash
# Sprawdź status
bash docker/scripts/logs.sh
```

### 4️⃣ Otwórz w przeglądarce

| Serwis | URL |
|--------|-----|
| Frontend (React) | http://localhost:5174 |
| Backend API | http://localhost:8000 |

### Rozwój

Zmiany w kodzie są automatycznie widoczne - nie trzeba restartować kontenerów!

```bash
# Backend shell (artisan, composer)
bash docker/scripts/shell.sh backend

# Frontend shell (npm)
bash docker/scripts/shell.sh frontend

# MariaDB shell
bash docker/scripts/shell.sh mariadb

# Redis shell
bash docker/scripts/shell.sh redis
```

### Zatrzymanie

```bash
bash docker/scripts/stop.sh
```

---

**Szczegóły**: Przeczytaj `DOCKER_SETUP.md` dla pełnej dokumentacji.

