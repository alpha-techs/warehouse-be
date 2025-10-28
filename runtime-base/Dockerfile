FROM php:8.4
LABEL authors="Li Chaoyi"

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    git \
    curl \
    zip \
    unzip \
 && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-configure intl

RUN docker-php-ext-install -j"$(nproc)" \
    gd \
    intl \
    mbstring \
    pdo_mysql \
    zip \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
