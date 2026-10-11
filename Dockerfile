FROM php:8.2-apache

# Install system dependencies & MongoDB PHP extension
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Allow Apache .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ensure data directory has proper permissions
RUN mkdir -p /var/www/html/data/db \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod -R 775 /var/www/html/data

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
