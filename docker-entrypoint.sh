#!/bin/bash
set -e

# Default port to 8080 if PORT is not set by hosting provider
PORT=${PORT:-8080}

# Ensure data directory is owned and writable
mkdir -p /var/www/html/data/db
chown -R www-data:www-data /var/www/html/data 2>/dev/null || true
chmod -R 775 /var/www/html/data 2>/dev/null || true

# Auto-seed database if empty
php /var/www/html/seed.php || true

echo "Starting CraveHub server on port ${PORT}..."
exec php -S 0.0.0.0:${PORT} /var/www/html/router.php
