########################
# 1️⃣ Node build (Inertia assets)
########################
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources resources
COPY vite.config.ts .
RUN npm run build


########################
# 2️⃣ Composer build
########################
FROM composer:2 AS composer-build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader




########################
# 3️⃣ Runtime image
########################
FROM php:8.2-apache

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Laravel backend
COPY --from=composer-build /app /var/www/html

# Inertia/Vite build output
COPY --from=node-build /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apachectl", "-D", "FOREGROUND"]



# # Working Dockerfile for Laravel 12 with PHP 8.2, Apache, Node.js, and npm
# # Use PHP 8.2 with Apache
# FROM php:8.2-apache

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Install Node.js 20 LTS & npm 11
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
#     apt-get install -y nodejs && \
#     npm install -g npm@11.2.0

# # Copy Apache configuration file
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# # Set ServerName to avoid errors
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Enable Apache modules for Laravel
# RUN a2enmod rewrite

# # Set working directory
# WORKDIR /var/www/html

# # Copy application code with correct ownership
# COPY --chown=www-data:www-data . /var/www/html

# # Ensure script is executable
# # RUN chmod +x /var/www/html/docker-entrypoint.sh

# # Install PHP dependencies WITH development dependencies like fakerphp/faker
# RUN git config --global --add safe.directory /var/www/html && \
#     composer install --optimize-autoloader

# # Install Node.js dependencies and build assets
# RUN npm ci && npm run build

# # Set final permissions for storage and cache
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Switch to www-data user to avoid permission issues
# USER www-data

# # Set entrypoint
# # Uncomment the line that start with "ENTRYPOINT" if you need to migrate or reset the database
# # ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

# # Expose port 80
# EXPOSE 80

# # Start Apache
# CMD ["apachectl", "-D", "FOREGROUND"]



# Mullti Build
# # ─── Stage 1: Composer dependencies ───────────────────────────────────────
# FROM composer:2 AS php-deps

# WORKDIR /app

# # Copy only what's needed for composer install to maximize cache hits
# COPY composer.json composer.lock ./
# COPY artisan ./artisan

# # Install only necessary PHP dependencies
# RUN composer install --no-dev --optimize-autoloader --no-scripts


# # ─── Stage 2: Final production image ──────────────────────────────────────
# FROM php:8.2-apache

# # Enable mod_rewrite and install system dependencies including Node.js and npm
# RUN a2enmod rewrite && \
#     apt-get update && apt-get install -y \
#     libpng-dev libjpeg-dev libfreetype6-dev \
#     zip unzip git curl libonig-dev \
#     nodejs npm && \
#     apt-get clean

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Configure Apache
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Set working directory
# WORKDIR /var/www/html

# # Copy entire app excluding ignored files
# COPY . .

# # Copy production-ready vendor from composer stage
# COPY --from=php-deps /app/vendor ./vendor

# # Install Node dependencies and build assets
# COPY package*.json ./
# RUN npm ci

# COPY resources ./resources
# COPY public ./public
# COPY vite.config.ts ./

# # Build Vite assets
# RUN npm run build

# # Fix permissions
# RUN chown -R www-data:www-data storage bootstrap/cache && \
#     chmod -R 775 storage bootstrap/cache

# # Use non-root user
# USER www-data

# # Set entrypoint
# # Uncomment the line that start with "ENTRYPOINT" if you need to migrate or reset the database
# ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

# EXPOSE 80
# CMD ["apachectl", "-D", "FOREGROUND"]


# # Use PHP 8.2 with Apache
# FROM php:8.2-apache

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Install Node.js 20 LTS & npm 11
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
#     apt-get install -y nodejs && \
#     npm install -g npm@11.2.0

# # Copy Apache configuration file
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# # Set ServerName to avoid errors
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Enable Apache modules for Laravel
# RUN a2enmod rewrite

# # Set working directory
# WORKDIR /var/www/html

# # Copy application code with correct ownership
# COPY --chown=www-data:www-data . /var/www/html

# # Ensure script is executable
# # RUN chmod +x /var/www/html/docker-entrypoint.sh

# # Install PHP dependencies WITH development dependencies like fakerphp/faker
# RUN git config --global --add safe.directory /var/www/html && \
#     composer install --optimize-autoloader

# # Install Node.js dependencies and build assets
# RUN npm ci && npm run build

# # Set final permissions for storage and cache
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Switch to www-data user to avoid permission issues
# USER www-data

# # Set entrypoint
# # Uncomment the line that start with "ENTRYPOINT" if you need to migrate or reset the database
# # ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

# # Expose port 80
# EXPOSE 80

# # Start Apache
# CMD ["apachectl", "-D", "FOREGROUND"]

# # Stage 1: Build the app using Node.js and Composer
# FROM php:8.2-apache AS builder

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Install system dependencies for PHP
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl \
#     && apt-get clean

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Install Node.js 20 LTS & npm 11
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
#     apt-get install -y nodejs && \
#     npm install -g npm@11.2.0

# # Set the working directory for the app
# WORKDIR /var/www/html

# # Copy the application code to the container with correct ownership
# COPY --chown=www-data:www-data . .

# # Install PHP dependencies (including dev dependencies)
# RUN git config --global --add safe.directory /var/www/html && \
#     composer install --optimize-autoloader

# # Install Node.js dependencies and build assets
# RUN npm ci && npm run build

# # Stage 2: Create the production-ready image with PHP and Apache
# FROM php:8.2-apache

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Install system dependencies for PHP (same as in the build stage)
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl \
#     && apt-get clean

# # Install PHP extensions (same as in the build stage)
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Copy Apache configuration file
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# # Set ServerName to avoid Apache errors
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Set working directory for the app
# WORKDIR /var/www/html

# # Copy the built application and assets from the builder stage
# COPY --from=builder /var/www/html /var/www/html

# # Set correct permissions for storage and cache
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Expose the application port (default is 80 for Apache)
# EXPOSE 80

# # Start Apache
# CMD ["apachectl", "-D", "FOREGROUND"]


# v1.0 has error but build the image and run the container
# # Use PHP 8.2 with Apache
# FROM php:8.2-apache

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Install Node.js 20 LTS & npm 11
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
#     apt-get install -y nodejs && \
#     npm install -g npm@11.1.0

# # Copy Apache configuration file
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# # Set ServerName to avoid errors
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Enable Apache modules for Laravel
# RUN a2enmod rewrite

# # Copy Laravel environment file
# COPY .env-db /var/www/html/.env

# # Set correct ownership for environment file
# RUN chown -R www-data:www-data /var/www/html/.env

# # Set working directory
# WORKDIR /var/www/html

# # Copy the application code with correct ownership
# COPY --chown=www-data:www-data . .

# # Ensure script is executable
# RUN chmod +x docker-entrypoint.sh

# # Install PHP dependencies WITH development dependencies like fakerphp/faker
# RUN git config --global --add safe.directory /var/www/html && \
#     composer install --optimize-autoloader

# # Install Node.js dependencies and build assets
# RUN npm ci && npm run build

# # Set final permissions for storage and cache
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Switch to www-data user to avoid permission issues
# USER www-data

# # Set entrypoint
# ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

# # Expose port 80
# EXPOSE 80

# # Start Apache
# CMD ["apachectl", "-D", "FOREGROUND"]









# # Use PHP 8.2 with Apache
# FROM php:8.2-apache

# # Enable Apache mod_rewrite
# # RUN a2enmod rewrite

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     libonig-dev \
#     curl

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd mbstring pdo pdo_mysql opcache



# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Install Node.js & NPM (Needed for Vue)
# # Install Node.js 20 LTS & npm 10
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
#     apt-get install -y nodejs && \
#     npm install -g npm@11.1.0
#     # npm install -g npm@10


# # Copy Apache configuration file
# COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# # Set ServerName to avoid errors
# RUN echo "ServerName testingapp.com" >> /etc/apache2/sites-available/000-default.conf

# # Enable Apache modules for Laravel
# RUN a2enmod rewrite

# COPY .env /var/www/html/.env

# RUN chown -R www-data:www-data /var/www/html/.env

# # Set working directory
# WORKDIR /var/www/html

# # # Copy the application code to the container
# # COPY . .
# COPY --chown=www-data:www-data . .

# # Ensure script is executable
# RUN chmod +x docker-entrypoint.sh

# # Install PHP dependencies WITHOUT the development depencencies like fakerphp/faker
# # RUN composer install --no-dev --optimize-autoloader
# # Set final permissions AFTER generating new files

# # Install PHP dependencies WITH the development depencencies like fakerphp/faker
# RUN git config --global --add safe.directory /var/www/html && \
#     composer install --optimize-autoloader

# # This speeds up installation by using
# # the exact versions in package-lock.json.
# RUN npm ci && npm run build

# # Critical as it will block permission if on composer i and npm i generated files
# # RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Set entrypoint
# ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

# # Expose port 80
# EXPOSE 80

# # Start Apache
# # CMD ["apachectl", "-D", "FOREGROUND"]
