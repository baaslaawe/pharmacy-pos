FROM php:7.3-apache AS builder

MAINTAINER Joe Nyugoh <joenyugoh@gmail.com>

RUN apt update -y && \
    apt install -y gnupg gnupg2 gnupg1 && \
    curl -sL https://deb.nodesource.com/setup_6.x | bash - && \
    apt install -y nodejs && \
    apt install -y git && \
    a2enmod proxy_http proxy_wstunnel rewrite && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    apt update && apt install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get auto-clean

WORKDIR /var/www/html

COPY . /var/www/html


COPY apache-config.conf /etc/apache2/sites-enabled/000-default.conf
COPY mariadb/dbconfig.json library/wpos/.dbconfig.json
COPY mariadb/config.json library/wpos/.config.json

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]