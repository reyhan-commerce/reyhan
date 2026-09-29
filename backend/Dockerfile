FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    linux-headers \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy configuration
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Dependencies stage
FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Final Production image
FROM base AS prod

COPY . /app
COPY --from=vendor /app/vendor /app/vendor

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80 443 443/udp 2019

ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=80", "--admin-port=2019", "--workers=auto", "--max-requests=1000"]
