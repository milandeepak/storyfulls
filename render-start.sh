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

# Always run Drupal deployment tasks on every deploy
echo "Starting Drupal deployment tasks..."

# Try to run deploy commands - will skip if DB not available
echo "Running drush deploy..."
./vendor/bin/drush deploy -y 2>/dev/null || {
  echo "⚠ Drush deploy skipped (DB might not be available yet)"
}

# S3FS has been disabled - now using local file storage
# Files are committed to GitHub repository instead of R2

# ALWAYS clear cache on every deployment - this ensures new routes are registered
echo "Clearing Drupal cache (new deployment)..."
./vendor/bin/drush cr 2>/dev/null || {
  echo "⚠ Cache clear skipped (Drupal not bootstrapped yet)"
}

echo "✓ Deployment tasks complete"

# Render sets PORT; nginx must listen on it
LISTEN_PORT="${PORT:-8080}"
sed "s/listen 8080/listen ${LISTEN_PORT}/" /etc/nginx/sites-available/default > /tmp/default.conf && mv /tmp/default.conf /etc/nginx/sites-available/default

# Start PHP-FPM in background, nginx in foreground
php-fpm --nodaemonize &
exec nginx -g "daemon off;"
