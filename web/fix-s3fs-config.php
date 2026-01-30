<?php
/**
 * Force update S3FS configuration to disable versioning
 * Run at: https://storyfulls-1.onrender.com/fix-s3fs-config.php
 */

header('Content-Type: text/plain');

echo "=== Forcing S3FS Configuration Update ===\n\n";

chdir('/var/www/html');

echo "Step 1: Set use_versioning to FALSE\n";
passthru('./vendor/bin/drush config:set s3fs.settings use_versioning FALSE -y 2>&1', $ret1);
echo "Exit code: $ret1\n\n";

echo "Step 2: Verify configuration\n";
passthru('./vendor/bin/drush config:get s3fs.settings use_versioning 2>&1', $ret2);
echo "\n\n";

echo "Step 3: Clear cache\n";
passthru('./vendor/bin/drush cr 2>&1', $ret3);
echo "\n\n";

echo "Step 4: Try refreshing S3FS cache again\n";
passthru('./vendor/bin/drush s3fs-refresh-cache -y 2>&1', $ret4);
echo "Exit code: $ret4\n\n";

if ($ret4 === 0) {
    echo "Step 5: Check file count\n";
    passthru('./vendor/bin/drush sqlq "SELECT COUNT(*) FROM s3fs_file" 2>&1', $ret5);
    echo "\n\n";
    
    echo "✓✓✓ SUCCESS! S3FS cache has been populated.\n";
    echo "Your images should now load from Cloudflare R2!\n";
} else {
    echo "✗ Cache refresh still failing. Checking configuration...\n";
    passthru('./vendor/bin/drush config:get s3fs.settings 2>&1');
}

echo "\n=== Done ===\n";
