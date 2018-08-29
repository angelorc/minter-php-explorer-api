FROM php:7.2.9-cli-alpine3.8

RUN apk --no-cache add \
    gmp-dev \
    curl \
    postgresql-dev \
    libmemcached-dev \
    libmcrypt-dev \
    libxml2-dev \
    zlib-dev \
    bzip2 \
    libpng-dev \
    imagemagick \
    optipng

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install gd
RUN docker-php-ext-install sockets
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql

WORKDIR /var/www