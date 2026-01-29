#!/usr/bin/env bash
set -e

cd /var/www/html

# Generate settings.php for Render (from default.settings.php + Render include)
if { [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; } && [ ! -f web/sites/default/settings.php ]; then
  echo "Generating settings.php for Render..."
  cp web/sites/default/default.settings.php web/sites/default/settings.php
  printf '\n<?php\n// Render.com environment\nif (getenv("DATABASE_URL") || getenv("DB_HOST")) { include __DIR__ . "/settings.render.php"; }\n' >> web/sites/default/settings.php
fi

# Dependencies are installed in Dockerfile so the container can listen on PORT quickly

# Ensure files directory exists and is writable
mkdir -p web/sites/default/files
chown -R www-data:www-data web/sites/default/files 2>/dev/null || true

# Run Drupal deploy (config import, updates) if DB is available; ignore failure on first deploy
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
  if ./vendor/bin/drush status bootstrap 2>/dev/null | grep -q "Successful"; then
    echo "Running drush deploy..."
    ./vendor/bin/drush deploy -y 2>/dev/null || true
    ./vendor/bin/drush cr 2>/dev/null || true
  fi
fi

# Render sets PORT; nginx must listen on it
LISTEN_PORT="${PORT:-8080}"
sed "s/listen 8080/listen ${LISTEN_PORT}/" /etc/nginx/sites-available/default > /tmp/default.conf && mv /tmp/default.conf /etc/nginx/sites-available/default

# Start PHP-FPM in background, nginx in foreground
php-fpm --nodaemonize &
exec nginx -g "daemon off;"
