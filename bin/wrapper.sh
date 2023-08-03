#!/bin/bash

# Start the first process
php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite && \
    service apache2 restart &

# Start the second process
ynetd -p 8085 /root/web_shell
