# syntax=docker/dockerfile:1.7

FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY components.json tsconfig.json vite.config.ts ./
COPY public ./public
COPY resources ./resources
COPY scripts ./scripts

RUN npm run build

FROM php:8.3-cli-bookworm AS app

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        chromium \
        git \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install bcmath intl mbstring pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN php artisan package:discover --ansi \
    && mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && if [ ! -L public/storage ]; then php artisan storage:link; fi \
    && chown -R www-data:www-data bootstrap/cache public storage \
    && chmod -R ug+rwx bootstrap/cache public storage

COPY docker/start.sh /usr/local/bin/start-qbus
RUN chmod +x /usr/local/bin/start-qbus

ENV APP_ENV=production \
    APP_DEBUG=false \
    BROWSER_BINARY=/usr/bin/chromium \
    LOG_CHANNEL=stack \
    LOG_STACK=stderr \
    PORT=10000

USER www-data

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/start-qbus"]
