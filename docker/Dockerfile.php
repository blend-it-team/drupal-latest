FROM php:8.3-cli-alpine3.20 AS build
WORKDIR /app

RUN apk add --no-cache \
git unzip \
icu-dev zlib-dev libzip-dev \
libpng-dev libjpeg-turbo-dev freetype-dev \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

FROM php:8.3-fpm-alpine3.20

RUN apk add --no-cache \
icu-dev zlib-dev libzip-dev \
libpng-dev libjpeg-turbo-dev freetype-dev \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql opcache

WORKDIR /var/www/html
COPY --from=build /app /var/www/html
