FROM php:8.3-apache

# PHP extensions install kar
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install zip mysqli pdo pdo_mysql

# Apache config
RUN a2enmod rewrite

# Working directory
WORKDIR /var/www/html

# Files copy kar
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Port
EXPOSE 80
