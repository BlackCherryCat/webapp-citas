FROM php:8.4-apache

# Instalar dependencias del sistema y extensiones requeridas
RUN apt-get update
RUN apt-get install -y \
    libzip-dev unzip libpng-dev libjpeg-dev libfreetype6-dev default-mysql-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) pdo_mysql mysqli gd zip opcache
RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

RUN rm -rf /var/lib/apt/lists/*

COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install

CMD ["apache2-foreground"]
