FROM php:8.1-fpm-bullseye

RUN apt update && apt install iputils-ping mtr traceroute -y

WORKDIR /var/www/html

COPY . .
COPY docker/php-fpm/src/config.php config.php
