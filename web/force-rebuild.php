<?php
/**
 * Force S3FS cache rebuild - simpler version
 */

set_time_limit(300); // 5 minutes
header('Content-Type: text/plain; charset=utf-8');

echo "=== S3FS Cache Force Rebuild ===\n\n";

// Step 1: Truncate
echo "[" . date('H:i:s') . "] Truncating cache table...\n";
system("cd /var/www/html && vendor/bin/drush sqlq 'TRUNCATE TABLE s3fs_file' 2>&1", $ret);
if ($ret === 0) {
    echo "✓ Truncated\n\n";
} else {
    echo "✗ Failed (code: $ret)\n\n";
}

// Step 2: Refresh cache
echo "[" . date('H:i:s') . "] Refreshing cache from R2 (this may take 1-2 minutes)...\n";
flush();
system("cd /var/www/html && vendor/bin/drush s3fs-refresh-cache -y 2>&1", $ret);
if ($ret === 0) {
    echo "\n✓ Cache refreshed\n\n";
} else {
    echo "\n✗ Failed (code: $ret)\n\n";
}

// Step 3: Count total
echo "[" . date('H:i:s') . "] Counting cache entries...\n";
system("cd /var/www/html && vendor/bin/drush sqlq 'SELECT COUNT(*) as total FROM s3fs_file' 2>&1");
echo "\n";

// Step 4: Count public://
echo "[" . date('H:i:s') . "] Counting public:// URIs...\n";
system("cd /var/www/html && vendor/bin/drush sqlq \"SELECT COUNT(*) as public_files FROM s3fs_file WHERE uri LIKE 'public://%'\" 2>&1");
echo "\n";

// Step 5: Show sample book covers
echo "[" . date('H:i:s') . "] Sample book cover URIs:\n";
system("cd /var/www/html && vendor/bin/drush sqlq \"SELECT uri FROM s3fs_file WHERE uri LIKE '%book%' LIMIT 10\" 2>&1");
echo "\n";

// Step 6: Clear Drupal cache
echo "[" . date('H:i:s') . "] Clearing Drupal cache...\n";
system("cd /var/www/html && vendor/bin/drush cr 2>&1", $ret);
if ($ret === 0) {
    echo "✓ Cache cleared\n\n";
} else {
    echo "✗ Failed (code: $ret)\n\n";
}

echo "[" . date('H:i:s') . "] === Complete ===\n";
