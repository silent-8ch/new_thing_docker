FROM php:8.1-apache

# Install extensions and tools
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && a2enmod rewrite

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files and install dependencies (if any)
COPY composer.json composer.lock* /var/www/
RUN if [ -f composer.json ]; then composer install --no-interaction --no-dev --optimize-autoloader; fi

# Copy code (mounted in dev by docker-compose volumes)
COPY public/ /var/www/html/
COPY src/ /var/www/src/

# Ensure permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

EXPOSE 80