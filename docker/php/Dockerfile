FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash git unzip icu-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
