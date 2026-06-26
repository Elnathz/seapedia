# ── Stage 1: JS/CSS build ────────────────────────────────────────────────────
FROM node:22-alpine AS node-build

WORKDIR /app

COPY package.json package-lock.json ./
# Harden npm against flaky network (same class of failure as composer below)
RUN npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci --prefer-offline --no-audit --no-fund

COPY . .
# DOCKER_BUILD=true tells vite.config to skip wayfinder plugin (generated files committed to repo)
RUN DOCKER_BUILD=true npm run build

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

# Flaky GitHub connectivity (common on ID VPS) makes a single composer install
# brittle: one zipball timeout fails the whole build. Tune timeouts + lower the
# parallel-download storm, then retry — the HTTP cache persists across attempts,
# so each retry only re-fetches the few packages that previously timed out.
ENV COMPOSER_PROCESS_TIMEOUT=900 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MAX_PARALLEL_HTTP=6

COPY composer.json composer.lock ./
RUN set -eu; \
    for i in 1 2 3 4 5; do \
        if composer install --no-dev --optimize-autoloader --no-scripts --no-progress --prefer-dist; then \
            exit 0; \
        fi; \
        echo ">>> composer install attempt $i timed out; retrying in 10s..." >&2; \
        sleep 10; \
    done; \
    echo ">>> composer install failed after 5 attempts" >&2; \
    exit 1

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
