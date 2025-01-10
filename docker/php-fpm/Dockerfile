FROM php:8.4-fpm-bullseye

RUN apt-get update && \
    apt-get --no-install-recommends -y install iputils-ping mtr traceroute iproute2 && \
    rm -rf /var/lib/apt/lists/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY docker/php-fpm/src/config.php config.php
