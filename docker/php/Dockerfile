FROM php:7.2-fpm

RUN \
 apt update && \
 apt install -y \
 libz-dev \
 libpq-dev \
 libssl-dev \
 libmcrypt-dev \
 libgmp-dev \
 build-essential \
 automake \
 pkg-config \
 libtool \
 libffi-dev \
 curl \
 iputils-ping

RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install bcmath

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install sockets

WORKDIR /var/www