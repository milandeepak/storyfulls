<?php
/**
 * Fix S3FS configuration and refresh cache with correct URIs
 * Run at: https://storyfulls-1.onrender.com/fix-s3fs-final.php
 */

header('Content-Type: text/plain');
set_time_limit(180);

echo "=== Final S3FS Fix ===\n\n";

chdir('/var/www/html');

// Step 1: Enable use_s3_for_public
echo "Step 1: Enabling S3 for public files\n";
passthru('./vendor/bin/drush config:set s3fs.settings use_s3_for_public TRUE -y 2>&1', $ret1);
echo "Exit code: $ret1\n\n";

// Step 2: Verify the setting
echo "Step 2: Verifying use_s3_for_public\n";
passthru('./vendor/bin/drush config:get s3fs.settings use_s3_for_public 2>&1');
echo "\n\n";

// Step 3: Clear all caches
echo "Step 3: Clearing all caches\n";
passthru('./vendor/bin/drush cr 2>&1');
echo "\n\n";

// Step 4: Clear S3FS cache table
echo "Step 4: Clearing existing S3FS cache\n";
passthru('./vendor/bin/drush sqlq "TRUNCATE TABLE s3fs_file" 2>&1');
echo "\n\n";

// Step 5: Refresh S3FS cache (this should now use public:// URIs)
echo "Step 5: Refreshing S3FS cache with correct URIs\n";
passthru('./vendor/bin/drush s3fs-refresh-cache -y 2>&1', $ret5);
echo "Exit code: $ret5\n\n";

if ($ret5 === 0) {
    echo "Step 6: Verify cache was populated correctly\n";
    passthru('./vendor/bin/drush sqlq "SELECT COUNT(*) as total FROM s3fs_file" 2>&1');
    echo "\n";
    passthru('./vendor/bin/drush sqlq "SELECT uri FROM s3fs_file WHERE uri LIKE \'public://book-covers/%\' LIMIT 5" 2>&1');
    echo "\n\n";
    
    echo "✓✓✓ SUCCESS! S3FS should now work correctly!\n";
    echo "\nNow run simple-file-check.php to verify URIs match\n";
} else {
    echo "✗ Cache refresh failed\n";
}

echo "\n=== Done ===\n";
