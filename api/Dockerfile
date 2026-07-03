# API Symfony — image de production (PHP-FPM).
# Caddy fait office de serveur web et transmet les requêtes PHP via FastCGI (port 9000).
FROM php:8.4-fpm-alpine

# Extensions requises : PostgreSQL (Doctrine), intl (Symfony), opcache (perf prod).
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev icu-dev \
 && docker-php-ext-install pdo_pgsql intl opcache \
 && apk add --no-cache libpq icu-libs \
 && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod
WORKDIR /app

# Dépendances d'abord (cache de build), sources ensuite.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative \
 && composer run-script --no-dev post-install-cmd \
 && chown -R www-data:www-data var
