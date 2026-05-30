# syntax=docker/dockerfile:1.7

FROM node:22-bookworm-slim AS node_runtime

FROM php:8.3-cli-bookworm AS app

WORKDIR /var/www/html

COPY --from=node_runtime /usr/local/bin/node /usr/local/bin/node
COPY --from=node_runtime /usr/local/lib/node_modules /usr/local/lib/node_modules

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
    && ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan package:discover --ansi \
    && PHP_BINARY=/usr/local/bin/php APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= node scripts/generate-wayfinder.mjs --with-form \
    && SKIP_WAYFINDER_GENERATE=true npm run build \
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
    PHP_BINARY=/usr/local/bin/php \
    LOG_CHANNEL=stack \
    LOG_STACK=stderr \
    PORT=10000

USER www-data

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/start-qbus"]
