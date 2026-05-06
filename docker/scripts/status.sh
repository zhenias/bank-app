#!/bin/bash

# Script to check status of all docker containers and services

echo "📊 Bank App Docker Status"
echo "========================="
echo ""

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

echo "📦 Container Status:"
$DOCKER_COMPOSE_CMD -f docker-compose.yml ps

echo ""
echo "📊 Port Status:"
if command -v lsof &> /dev/null; then
    PORTS=(8000 5174 3306 6379)
    for port in "${PORTS[@]}"; do
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null ; then
            echo "  Port $port: ✅ In Use"
        else
            echo "  Port $port: ⏳ Not listening"
        fi
    done
else
    echo "  (lsof not available - run: brew install lsof)"
fi

echo ""
echo "🔗 Services:"
echo "  Backend API:  http://localhost:8000"
echo "  Frontend:     http://localhost:5174"
echo "  MariaDB:      localhost:3306"
echo "  Redis:        localhost:6379"

echo ""
echo "💡 Tips:"
echo "  View logs:    bash docker/scripts/logs.sh"
echo "  Open shell:   bash docker/scripts/shell.sh <service>"
echo "  Stop:         bash docker/scripts/stop.sh"

