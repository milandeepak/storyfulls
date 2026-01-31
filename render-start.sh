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
  echo "Waiting for database connection..."
  
  # Wait up to 30 seconds for database to be ready
  for i in {1..30}; do
    if ./vendor/bin/drush sqlq "SELECT 1" 2>/dev/null | grep -q "1"; then
      echo "✓ Database connection established"
      break
    fi
    echo "Waiting for database... attempt $i/30"
    sleep 1
  done
  
  echo "Checking Drupal bootstrap status..."
  if ./vendor/bin/drush status bootstrap 2>/dev/null | grep -q "Successful"; then
    echo "✓ Drupal bootstrapped successfully"
    
    echo "Running drush deploy..."
    ./vendor/bin/drush deploy -y 2>/dev/null || true
    
    # S3FS has been disabled - now using local file storage
    # Files are committed to GitHub repository instead of R2
    
    echo "Clearing Drupal cache..."
    ./vendor/bin/drush cr 2>/dev/null || true
    
    echo "✓ Drupal setup complete"
  else
    echo "⚠ Drupal bootstrap failed, skipping setup steps"
  fi
fi

# Render sets PORT; nginx must listen on it
LISTEN_PORT="${PORT:-8080}"
sed "s/listen 8080/listen ${LISTEN_PORT}/" /etc/nginx/sites-available/default > /tmp/default.conf && mv /tmp/default.conf /etc/nginx/sites-available/default

# Start PHP-FPM in background, nginx in foreground
php-fpm --nodaemonize &
exec nginx -g "daemon off;"
