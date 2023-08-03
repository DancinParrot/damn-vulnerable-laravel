#!/bin/bash

# Start the first process
php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ &

# Start the second process
ynetd -p 8085 /web_shell
