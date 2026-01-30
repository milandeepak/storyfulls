#!/usr/bin/env bash
# Enable and configure S3FS module for Cloudflare R2

set -e

echo "Enabling S3FS module..."
./vendor/bin/drush pm:enable s3fs -y

echo "Configuring S3FS for Cloudflare R2..."
./vendor/bin/drush config:set s3fs.settings use_s3_for_public TRUE -y
./vendor/bin/drush config:set s3fs.settings use_s3_for_private FALSE -y
./vendor/bin/drush config:set s3fs.settings root_folder 'drupal-files' -y
./vendor/bin/drush config:set s3fs.settings use_customhost TRUE -y
./vendor/bin/drush config:set s3fs.settings use_cname TRUE -y
./vendor/bin/drush config:set s3fs.settings domain 'https://pub-421b25a0828946dda54e908e094bc6a2.r2.dev' -y
./vendor/bin/drush config:set s3fs.settings use_path_style_endpoint FALSE -y
./vendor/bin/drush config:set s3fs.settings cache_control_header 'public, max-age=31536000' -y

echo "Refreshing S3FS file metadata cache..."
./vendor/bin/drush s3fs-refresh-cache

echo "Clearing Drupal cache..."
./vendor/bin/drush cr

echo ""
echo "✓ S3FS module enabled and configured!"
echo ""
echo "Your book images should now load from Cloudflare R2:"
echo "https://pub-421b25a0828946dda54e908e094bc6a2.r2.dev/drupal-files/book_covers/"
echo ""
echo "Test the site at: https://storyfulls-1.onrender.com"
