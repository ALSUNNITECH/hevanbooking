FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY dev/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

ENV DB_HOST=db \
    DB_USER=hevan \
    DB_PASS=hevanpass \
    DB_NAME=hevan_booking

EXPOSE 80
