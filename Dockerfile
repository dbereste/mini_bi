# ---- 1. Generate the SQLite warehouse -------------------------------------
FROM python:3.12-slim AS data
WORKDIR /build
COPY scripts/generate_data.py scripts/generate_data.py
COPY database/schema/schema.sql database/schema/schema.sql
ARG SCALE=1.0
RUN SCALE=${SCALE} python3 -I scripts/generate_data.py /build/database.sqlite

# ---- 2. PHP dependencies -------------------------------------------------
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist --no-progress --ignore-platform-reqs

# ---- 3. Runtime ----------------------------------------------------------
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip sqlite3 \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-mini-bi.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/mini-bi-entrypoint
RUN chmod +x /usr/local/bin/mini-bi-entrypoint

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN composer dump-autoload --optimize --no-scripts \
    && cp -n .env.example .env \
    && chown -R www-data:www-data storage bootstrap/cache

RUN mkdir -p /data && chown www-data:www-data /data
COPY --from=data --chown=www-data:www-data /build/database.sqlite /data/database.sqlite

ENV DB_DATABASE=/data/database.sqlite

EXPOSE 80
ENTRYPOINT ["mini-bi-entrypoint"]
CMD ["apache2-foreground"]
