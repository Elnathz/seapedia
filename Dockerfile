# ── Stage 1: JS/CSS build ────────────────────────────────────────────────────
FROM node:22-alpine AS node-build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

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

RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    icu-libs \
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

# Copy the built static assets from node-build so nginx can serve them directly
COPY --from=node-build /app/public ./
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
