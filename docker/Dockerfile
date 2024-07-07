FROM php:8.2-fpm

RUN apt-get update && \
  apt-get install -y \
  apt-utils git zip unzip curl vim \
  libxml2-dev \
  libpq-dev \
  libzip-dev \
  && docker-php-ext-install ctype iconv session simplexml pdo_mysql mysqli pdo_pgsql zip

RUN pecl install xdebug && \
  docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN mv /usr/local/bin/composer /usr/bin/composer

WORKDIR /var/www/html