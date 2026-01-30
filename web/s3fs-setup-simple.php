<?php
/**
 * Simple S3FS setup via exec() - no Drupal bootstrap needed
 * Access at: https://storyfulls-1.onrender.com/s3fs-setup-simple.php
 */

header('Content-Type: text/plain');
set_time_limit(120);

echo "=== Simple S3FS Setup (No Bootstrap) ===\n\n";

// Change to project root
chdir('/var/www/html');

echo "Step 1: Enable S3FS module\n";
echo "Running: drush pm:enable s3fs -y\n";
passthru('./vendor/bin/drush pm:enable s3fs -y 2>&1', $ret1);
echo "Exit code: $ret1\n\n";

echo "Step 2: Check if module is enabled\n";
echo "Running: drush pm:list --status=enabled | grep s3fs\n";
passthru('./vendor/bin/drush pm:list --status=enabled 2>&1 | grep s3fs', $ret2);
echo "\n\n";

echo "Step 3: Refresh S3FS cache\n";
echo "Running: drush s3fs-refresh-cache -y\n";
passthru('./vendor/bin/drush s3fs-refresh-cache -y 2>&1', $ret3);
echo "Exit code: $ret3\n\n";

echo "Step 4: Check cache count\n";
echo "Running: drush sqlq \"SELECT COUNT(*) FROM s3fs_file\"\n";
passthru('./vendor/bin/drush sqlq "SELECT COUNT(*) FROM s3fs_file" 2>&1', $ret4);
echo "\n\n";

echo "Step 5: Test file URL\n";
echo "Running: drush php:eval \"echo file_create_url('public://book-covers/1.jpg');\"\n";
passthru('./vendor/bin/drush php:eval "echo file_create_url(\'public://book-covers/1.jpg\');" 2>&1', $ret5);
echo "\n\n";

echo "Step 6: Clear cache\n";
echo "Running: drush cr\n";
passthru('./vendor/bin/drush cr 2>&1', $ret6);
echo "Exit code: $ret6\n\n";

echo "=== Setup Complete ===\n";
echo "Visit https://storyfulls-1.onrender.com to check if images load\n";
