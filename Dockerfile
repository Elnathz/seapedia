# ── Stage 1: JS/CSS build ────────────────────────────────────────────────────
FROM node:22-alpine AS node-build

# Wayfinder vite plugin calls `php artisan wayfinder:generate` during build
RUN apk add --no-cache php83 php83-cli php83-phar php83-openssl php83-tokenizer php83-mbstring php83-xml php83-xmlwriter php83-dom php83-json \
    && ln -sf /usr/bin/php83 /usr/bin/php

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Need composer vendor for artisan to work
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --quiet

COPY . .
RUN npm run build

# ── Stage 2: PHP deps (no devtools) ──────────────────────────────────────────
FROM php:8.3-fpm-alpine AS php-deps

WORKDIR /app

RUN apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ── Stage 3: php-fpm production image ────────────────────────────────────────
FROM php:8.3-fpm-alpine AS production

WORKDIR /app

# Runtime libs only — extensions copied from php-deps, no recompile needed
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    icu-libs

COPY --from=php-deps /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-deps /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=php-deps /app/vendor ./vendor
COPY --from=node-build /app/public/build ./public/build
COPY . .

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

# ── Stage 4: nginx with baked static files ────────────────────────────────────
FROM nginx:1.27-alpine AS nginx-web

WORKDIR /app/public

COPY --from=node-build /app/public ./
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
