##############
# Base image #
##############
FROM php:8.4-fpm-alpine AS base

ARG UID
ARG GID

WORKDIR /srv/app

# Install the PHP extensions build dependencies
# $PHPIZE_DEPS = autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c
RUN set -eu; apk add --no-cache --virtual .build-deps  $PHPIZE_DEPS

# Install the needed commands and PHP extensions
RUN set -eu; \
    apk add --no-cache bash git curl nano \
        libxml2-dev \
        oniguruma-dev \
        icu-dev \
        zlib-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        sqlite-dev \
        postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mbstring \
        bcmath \
        calendar \
        exif \
        gd \
        intl \
        pcntl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
        bz2 \
        dom \
        simplexml \
        opcache \
    && pecl install ds \
    && docker-php-ext-enable ds

# Uninstall build dependencies and any apk cache
RUN set -eu; \
    apk del .build-deps \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    rm "$PHP_INI_DIR/php.ini-production"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/

RUN addgroup -S www-user -g $GID && \
    adduser -S www-user -G www-user -u $UID

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

#########################
# Web application image #
#########################
FROM base AS php-fpm

COPY docker/php/php-fpm.d/website.conf /usr/local/etc/php-fpm.d/website.conf
RUN mkdir /var/run/php && chown -R www-user:www-user /var/run/php
USER www-user
ENTRYPOINT ["php-fpm"]


###############################
# Caddy (reverse proxy) image #
###############################
FROM caddy:2-builder-alpine AS caddy_builder

RUN xcaddy build

FROM caddy:2-alpine AS caddy
RUN set -eux; \
    apk add --no-cache nss-tools
WORKDIR /srv/app
COPY --from=caddy_builder /usr/bin/caddy /usr/bin/caddy
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
