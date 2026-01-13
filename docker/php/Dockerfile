FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Laravel source into image
COPY edutrack/ .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create Laravel runtime directories
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/views \
    storage/framework/sessions \
    bootstrap/cache

# Correct ownership & permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Run PHP as non-root
USER www-data
