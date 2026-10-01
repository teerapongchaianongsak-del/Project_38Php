FROM php:8.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx gettext-base libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/* /etc/nginx/sites-enabled/default

# ให้ PHP-FPM มองเห็นตัวแปร PGHOST/PGUSER/... ของ Railway
RUN printf '[www]\nclear_env = no\n' > /usr/local/etc/php-fpm.d/zz-env.conf

WORKDIR /var/www/html
COPY src/ /var/www/html/
COPY nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY nginx/start.sh /start.sh
RUN sed -i 's/\r$//' /start.sh && chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
