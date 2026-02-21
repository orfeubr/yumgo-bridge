FROM php:8.3-fpm-alpine
RUN apk add --no-cache git curl libpng-dev libzip-dev zip unzip postgresql-dev icu-dev oniguruma-dev supervisor
RUN docker-php-ext-install pdo_pgsql pgsql mbstring zip exif pcntl bcmath intl opcache
RUN apk add --no-cache $PHPIZE_DEPS && pecl install redis && docker-php-ext-enable redis
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/restaurante
RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
COPY php.ini /usr/local/etc/php/
USER www
EXPOSE 9000
CMD ["php-fpm"]
