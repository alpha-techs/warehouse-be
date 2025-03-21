FROM php:8.4
LABEL authors="Li Chaoyi"

RUN apt-get update && apt-get install -y libicu-dev libonig-dev git curl zip unzip
RUN docker-php-ext-configure intl
RUN docker-php-ext-install -j5 intl mbstring pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
