#!/usr/bin/env bash
set -e

cd /var/www/html

# Generate settings.php for Render (from default.settings.php + Render include)
if [ -n "$DATABASE_URL" ] && [ ! -f web/sites/default/settings.php ]; then
  echo "Generating settings.php for Render..."
  cp web/sites/default/default.settings.php web/sites/default/settings.php
  printf '\n<?php\n// Render.com environment\nif (getenv("DATABASE_URL")) { include __DIR__ . "/settings.render.php"; }\n' >> web/sites/default/settings.php
fi

# Install Composer dependencies (no dev for production)
echo "Running composer install..."
composer install --no-dev --no-interaction --optimize-autoloader --working-dir=/var/www/html

# Ensure files directory exists and is writable
mkdir -p web/sites/default/files
chown -R www-data:www-data web/sites/default/files 2>/dev/null || true

# Run Drupal deploy (config import, updates) if DB is available; ignore failure on first deploy
if [ -n "$DATABASE_URL" ]; then
  if ./vendor/bin/drush status bootstrap 2>/dev/null | grep -q "Successful"; then
    echo "Running drush deploy..."
    ./vendor/bin/drush deploy -y 2>/dev/null || true
    ./vendor/bin/drush cr 2>/dev/null || true
  fi
fi

# Start nginx + PHP-FPM (from base image)
exec /start.sh
