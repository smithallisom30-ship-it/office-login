FROM php:8.4-cli

RUN apt-get update && apt-get install -y pkg-config libssl-dev unzip git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

CMD php -S 0.0.0.0:${PORT:-10000}