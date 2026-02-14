# Stage 1: Base
FROM php:8.5-fpm-alpine AS base

RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    git \
    unzip \
    && apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make

# Note: mbstring is built-in since PHP 8.4
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache

RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Stage 2: Development
FROM base AS development

RUN apk add --no-cache --virtual .build-deps autoconf g++ make linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY docker/php-fpm/php-dev.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# Stage 3: Production
FROM base AS production

COPY --chown=www-data:www-data . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

COPY docker/php-fpm/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
