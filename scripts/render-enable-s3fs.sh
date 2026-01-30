#!/usr/bin/env bash
# Run this script in Render Shell to enable S3FS and refresh cache
# Go to: https://dashboard.render.com -> Your Service -> Shell

set -e

echo "=== Checking Drupal Status ==="
cd /var/www/html
./vendor/bin/drush status

echo ""
echo "=== Enabling S3FS Module ==="
./vendor/bin/drush pm:enable s3fs -y

echo ""
echo "=== Verifying S3FS Configuration ==="
./vendor/bin/drush config:get s3fs.settings

echo ""
echo "=== Refreshing S3FS File Cache ==="
echo "This will scan your R2 bucket and cache all file metadata..."
./vendor/bin/drush s3fs-refresh-cache

echo ""
echo "=== Checking S3FS Cache Status ==="
./vendor/bin/drush sqlq "SELECT COUNT(*) as file_count FROM s3fs_file"

echo ""
echo "=== Clearing Drupal Cache ==="
./vendor/bin/drush cr

echo ""
echo "=== Testing File URL Generation ==="
./vendor/bin/drush php:eval "echo file_create_url('public://book_covers/test.jpg');"

echo ""
echo ""
echo "✓ S3FS setup complete!"
echo "Visit your site to verify images are loading from R2."
