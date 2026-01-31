<?php
/**
 * Rebuild S3FS cache after path configuration changes
 * Run this after updating public_folder or root_folder settings
 */

echo "=== Rebuilding S3FS Cache ===\n\n";

// Clear existing cache
echo "1. Truncating s3fs_file cache table...\n";
passthru("vendor/bin/drush sqlq 'TRUNCATE TABLE s3fs_file'", $result);
if ($result === 0) {
    echo "   ✓ Cache table truncated\n\n";
} else {
    echo "   ✗ Failed to truncate cache table\n\n";
    exit(1);
}

// Refresh S3FS cache
echo "2. Refreshing S3FS cache from R2...\n";
passthru("vendor/bin/drush s3fs-refresh-cache -y", $result);
if ($result === 0) {
    echo "   ✓ Cache refreshed successfully\n\n";
} else {
    echo "   ✗ Failed to refresh cache\n\n";
    exit(1);
}

// Clear Drupal caches
echo "3. Clearing Drupal caches...\n";
passthru("vendor/bin/drush cr", $result);
if ($result === 0) {
    echo "   ✓ Drupal caches cleared\n\n";
} else {
    echo "   ✗ Failed to clear caches\n\n";
    exit(1);
}

// Verify cache rebuild
echo "4. Verifying cache rebuild...\n";
passthru("vendor/bin/drush sqlq \"SELECT COUNT(*) as total FROM s3fs_file\"", $result);
echo "\n";

echo "5. Checking for book covers in cache...\n";
passthru("vendor/bin/drush sqlq \"SELECT COUNT(*) as book_covers FROM s3fs_file WHERE uri LIKE 'public://book-covers%'\"", $result);
echo "\n";

echo "=== Cache Rebuild Complete ===\n";
echo "Now check: https://storyfulls-1.onrender.com/simple-file-check.php\n";
