FROM php:8.4-fpm-alpine

WORKDIR /app

RUN apk add --no-cache git curl zip unzip mariadb-client nginx supervisor

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf
COPY ./docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

RUN mkdir -p storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache

COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
