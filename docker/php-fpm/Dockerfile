FROM php:8.1-fpm-bullseye

RUN apt-get update && \
    apt-get --no-install-recommends -y install iputils-ping mtr traceroute iproute2 && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY docker/php-fpm/src/config.php config.php
