FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader

COPY submit.php .

COPY . .