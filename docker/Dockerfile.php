# ---- Build stage: PHP CLI + required extensions + Composer
FROM php:8.3-cli-alpine AS build
WORKDIR /app

# Tools composer needs + PHP extensions commonly required by Drupal deps
RUN apk add --no-cache \
git unzip icu-dev zlib-dev libzip-dev \
libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
COMPOSER_MEMORY_LIMIT=-1

# Better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Copy the rest + run again for scaffold/autoload consistency
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# ---- Runtime stage: PHP-FPM
FROM php:8.3-fpm-alpine

RUN apk add --no-cache icu-dev zlib-dev libzip-dev \
libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev mariadb-client \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) pdo_mysql intl zip gd opcache

WORKDIR /var/www/html
COPY --from=build /app /var/www/html

RUN mkdir -p /var/www/html/web/sites/default/files \
&& chown -R www-data:www-data /var/www/html/web/sites/default/files
