FROM composer:latest as build-composer
COPY . /app/
RUN composer update
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

FROM node:18.17.0-bullseye as build
WORKDIR /app
COPY --from=build-composer /app .
RUN npm install
RUN npm run build

FROM php:8.2-apache-bullseye as production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=build /app /var/www/html
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .env /var/www/html/.env

# Update symlink
RUN php artisan storage:link

# DB Migrations
RUN php artisan migrate --force

RUN php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite
