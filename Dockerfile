# Drupal 11 on Render - PHP 8.3 + nginx (lock file requires PHP >= 8.3)
# Document root is web/
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    unzip \
    git \
    default-mysql-client \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql pdo_mysql mysqli gd zip opcache mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Nginx: listen on 8080, docroot web/ (we start with "nginx -g daemon off;" so no edit to nginx.conf)
COPY render-nginx-default.conf /etc/nginx/sites-available/default

COPY . .

# Install dependencies at build time so container starts quickly (Render port check)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-interaction --optimize-autoloader

ENV WEBROOT=/var/www/html/web
ENV APP_ENV=production

COPY render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD ["/render-start.sh"]
