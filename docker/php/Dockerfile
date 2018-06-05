FROM php:7.2-fpm

RUN \
 apt update && \
 apt install -y \
 curl \
 libz-dev \
 libpq-dev \
 libssl-dev \
 libmcrypt-dev \
 libgmp-dev

RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install bcmath

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install sockets

WORKDIR /var/www