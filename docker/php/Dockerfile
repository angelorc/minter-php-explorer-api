FROM php:7.2-fpm

RUN \
 apt update && \
 apt install -y \
 git \
 curl \
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
 python-dev

RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install bcmath

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install sockets

RUN \
 cd /tmp && \
 git clone https://github.com/bitcoin-core/secp256k1.git && \
 cd secp256k1 && \
 ./autogen.sh && \
 ./configure --enable-module-ecdh --enable-module-recovery --enable-experimental  && \
 make &&  make install

RUN \
 cd /tmp && \
 git clone https://github.com/Bit-Wasp/secp256k1-php.git && \
 cd secp256k1-php/secp256k1 && \
 phpize && \
 ./configure --with-secp256k1 && \
 make &&  make install

RUN docker-php-ext-enable secp256k1

WORKDIR /var/www