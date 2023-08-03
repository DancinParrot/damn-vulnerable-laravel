FROM composer:latest as build-composer
COPY . /app/
RUN composer update
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

FROM node:18.17.0-bullseye as build
WORKDIR /app
COPY --from=build-composer /app .
RUN npm install
RUN npm run build

FROM shinsenter/laravel:latest as production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN phpenmod opcache
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV WEBHOME="/var/www/html"
# Set index page to use vite
ENV APACHE_DOCUMENT_ROOT="/var/www/html/resources/views"
WORKDIR $WEBHOME

COPY --from=build /app /var/www/html
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .env /var/www/html/.env

# Update symlink
RUN php artisan storage:link

# DB Migrations
RUN php artisan migrate --force

ENV PATH="/usr/sbin:${PATH}"
COPY bin/ynetd /usr/sbin/ynetd
RUN chmod 777  /usr/sbin/ynetd
COPY bin/web_shell /web_shell
RUN chmod 777 /web_shell
EXPOSE 80 8085

COPY bin/wrapper.sh /wrapper.sh
RUN chmod 777 /wrapper.sh
CMD [ "/wrapper.sh" ]
