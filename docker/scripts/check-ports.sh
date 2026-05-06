#!/bin/bash

# Script to check if required ports are available

PORTS=(8000 5174 3306 6379)
AVAILABLE=true

echo "🔍 Checking if required ports are available..."
echo ""

for port in "${PORTS[@]}"; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null ; then
        echo "❌ Port $port is already in use!"
        AVAILABLE=false
    else
        echo "✅ Port $port is available"
    fi
done

echo ""
if [ "$AVAILABLE" = true ]; then
    echo "✅ All ports are available. You can start Docker!"
else
    echo "❌ Some ports are in use. Please free them up and try again."
    exit 1
fi

