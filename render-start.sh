#!/usr/bin/env bash
set -e

cd /var/www/html

# Settings.php is now managed in Git and includes settings.render.php automatically
# No need to generate it dynamically anymore

# Dependencies are installed in Dockerfile so the container can listen on PORT quickly

# Ensure files directory exists and is writable
mkdir -p web/sites/default/files
chown -R www-data:www-data web/sites/default/files 2>/dev/null || true

# Create required writable directories in /tmp for ephemeral storage
mkdir -p /tmp/drupal-sync /tmp/private
chown -R www-data:www-data /tmp/drupal-sync /tmp/private 2>/dev/null || true

# Run Drupal deploy (config import, updates) if DB is available; ignore failure on first deploy
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
  if ./vendor/bin/drush status bootstrap 2>/dev/null | grep -q "Successful"; then
    echo "Running drush deploy..."
    ./vendor/bin/drush deploy -y 2>/dev/null || true
    
    # Enable S3FS module if not already enabled
    if ! ./vendor/bin/drush pm:list --status=enabled --type=module 2>/dev/null | grep -q "s3fs"; then
      echo "Enabling S3FS module..."
      ./vendor/bin/drush pm:enable s3fs -y 2>/dev/null || true
    fi
    
    # Configure S3FS if R2 credentials are available
    if [ -n "$R2_BUCKET" ] && [ -n "$R2_ENDPOINT" ]; then
      echo "Configuring S3FS for Cloudflare R2..."
      ./vendor/bin/drush s3fs-refresh-cache 2>/dev/null || true
    fi
    
    ./vendor/bin/drush cr 2>/dev/null || true
  fi
fi

# Render sets PORT; nginx must listen on it
LISTEN_PORT="${PORT:-8080}"
sed "s/listen 8080/listen ${LISTEN_PORT}/" /etc/nginx/sites-available/default > /tmp/default.conf && mv /tmp/default.conf /etc/nginx/sites-available/default

# Start PHP-FPM in background, nginx in foreground
php-fpm --nodaemonize &
exec nginx -g "daemon off;"
