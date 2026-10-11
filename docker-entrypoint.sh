#!/bin/bash
set -e

# Default port to 80 if PORT is not set by hosting provider
PORT=${PORT:-80}

# Update Apache configuration to bind to the assigned port (Render uses $PORT)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure data directory is owned and writable by Apache user
mkdir -p /var/www/html/data/db
chown -R www-data:www-data /var/www/html/data
chmod -R 775 /var/www/html/data

# Auto-seed database if empty
php /var/www/html/seed.php || true

# Run Apache in foreground
exec apache2-foreground
