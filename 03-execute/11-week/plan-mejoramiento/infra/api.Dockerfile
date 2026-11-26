
FROM composer:2 AS dependencies

WORKDIR /app

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.3-cli-alpine

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

COPY --from=dependencies /app/vendor ./vendor

COPY . .

# Eliminar manifiestos de paquetes antiguos copiados desde el equipo local.
# Se regeneran con las dependencias realmente instaladas en esta imagen (--no-dev).
RUN rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi

EXPOSE 8000

