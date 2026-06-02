# syntax=docker/dockerfile:1
FROM php:8.4-fpm-alpine

# 1. System dependencies
RUN apk add --no-cache \
    git \
    curl \
    bash \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    zip \
    libzip-dev \
    unzip \
    shadow \
    supervisor

# 2. PHP extensions required by Laravel + the CRM package set
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# 3. Redis extension (queue/cache/horizon/reverb scaling)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# 4. PHP-FPM listens on all interfaces (nginx connects over the docker network).
#    The pool stays as www-data; the dev bind mount is made world-writable by the
#    compose entrypoint so the worker can write storage/ and bootstrap/cache.
RUN sed -i 's|listen = 127.0.0.1:9000|listen = 0.0.0.0:9000|' \
    /usr/local/etc/php-fpm.d/www.conf

# 5. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 6. PHP config
COPY ./php/local.ini /usr/local/etc/php/conf.d/local.ini

# 7. App code (dev uses a bind mount that overrides this; kept for prod parity)
COPY . .

RUN mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 8. Supervisor config (php-fpm)
COPY ./supervisor/supervisord.conf /etc/supervisord.conf

EXPOSE 9000
