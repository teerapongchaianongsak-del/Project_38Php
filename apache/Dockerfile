FROM php:8.3-apache

RUN docker-php-ext-install mysqli

# ล้าง MPM ทุกตัวที่อาจถูกเปิดอยู่
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY apache/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
