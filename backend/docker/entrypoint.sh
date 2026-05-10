#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# Prepare PHP-FPM directories
mkdir -p /var/run/php-fpm /var/log/php-fpm
chown -R nobody:nobody /var/run/php-fpm /var/log/php-fpm

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "❌ ERROR: No composer.json found!"
    exit 1
fi

# Check if vendor/autoload.php exists
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
    echo "✅ Composer dependencies installed"
fi

# Generate app key if not exists
if [ ! -f .env ]; then
    echo "📄 Creating .env from .env.example..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Cache configuration
echo "🔧 Caching configuration..."
php artisan optimize:clear

echo ""
echo "✅ Laravel application is ready!"
echo ""

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
