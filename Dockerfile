FROM php:8.5-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions separately
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install curl
RUN docker-php-ext-install gd

# Verify DOM is available for Dompdf
RUN php -m | grep -i '^dom$'

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Apache for Render's default web service port
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy MediTrack project
COPY . /var/www/html

# Install locked Composer dependencies
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 10000
