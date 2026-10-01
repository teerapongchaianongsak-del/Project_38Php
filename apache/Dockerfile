FROM php:8.3-apache

RUN docker-php-ext-install mysqli

RUN a2dismod mpm_event mpm_worker mpm_prefork || true \
    && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html

COPY apache/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
