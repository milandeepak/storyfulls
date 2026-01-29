# Drupal 11 on Render - PHP 8.3 + nginx (lock file requires PHP >= 8.3)
# Document root is web/
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    unzip \
    git \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql gd zip opcache mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Nginx: listen on 8080, docroot web/
COPY render-nginx-default.conf /etc/nginx/sites-available/default
RUN echo "daemon off;" >> /etc/nginx/nginx.conf

COPY . .

ENV WEBROOT=/var/www/html/web
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production

COPY render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD ["/render-start.sh"]
