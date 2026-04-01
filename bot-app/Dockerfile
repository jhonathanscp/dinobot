FROM serversideup/php:8.5-fpm-nginx-alpine

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

RUN composer install --no-interaction --optimize-autoloader --no-dev

RUN php artisan key:generate && php artisan config:cache && php artisan route:cache
