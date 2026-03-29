FROM richarvey/nginx-php-fpm:3.1.6

ENV WEBROOT /var/www/html/public
ENV SKIP_COMPOSER 1
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

ENV APP_ENV production
ENV APP_DEBUG false

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# IMPORTANT: Remove caching from build
# RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

RUN php artisan storage:link
