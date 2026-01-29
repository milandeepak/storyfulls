# Drupal 11 on Render - nginx + PHP-FPM
# Document root is web/ (Drupal's index.php lives in web/)
FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

# Copy entire project (respects .dockerignore)
COPY . .

# Drupal document root is web/, not project root
ENV WEBROOT=/var/www/html/web
ENV SKIP_COMPOSER=1
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

# Production
ENV APP_ENV=production

# Start script runs composer install + drush deploy + cache rebuild
COPY render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD ["/render-start.sh"]
