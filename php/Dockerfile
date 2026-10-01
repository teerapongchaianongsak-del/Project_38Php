FROM php:8.3-fpm

# ติดตั้ง nginx + driver ของ PostgreSQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/* /etc/nginx/sites-enabled/default

# php-fpm ปกติล้างตัวแปร environment ต้องปิด ไม่งั้น getenv("PGHOST") จะว่าง
RUN printf '[www]\nclear_env = no\nlisten = 127.0.0.1:9000\n' > /usr/local/etc/php-fpm.d/zz-env.conf

COPY nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && exec nginx -g 'daemon off;'"]
