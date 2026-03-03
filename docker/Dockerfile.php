# ---- Build stage (composer install)
FROM composer:2 AS build
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
&& composer clear-cache

# ---- Runtime stage (PHP-FPM)
FROM php:8.3-fpm-alpine

# PHP extensions commonly needed by Drupal 11
RUN apk add --no-cache icu-dev zlib-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev mariadb-client \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) pdo_mysql intl zip gd opcache

WORKDIR /var/www/html
COPY --from=build /app /var/www/html

# Ensure writable directory exists
RUN mkdir -p /var/www/html/web/sites/default/files \
&& chown -R www-data:www-data /var/www/html/web/sites/default/files
