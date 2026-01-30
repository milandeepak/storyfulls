<?php
/**
 * Configure S3FS completely via drush config:set
 * Run at: https://storyfulls-1.onrender.com/configure-s3fs-complete.php
 */

header('Content-Type: text/plain');
set_time_limit(180);

echo "=== Complete S3FS Configuration ===\n\n";

chdir('/var/www/html');

$config_commands = [
    ['bucket', getenv('R2_BUCKET')],
    ['region', 'auto'],
    ['use_customhost', 'TRUE'],
    ['hostname', getenv('R2_ENDPOINT')],
    ['use_cname', 'TRUE'],
    ['domain', getenv('R2_PUBLIC_URL')],
    ['use_path_style_endpoint', 'FALSE'],
    ['root_folder', 'drupal-files'],
    ['use_https', 'TRUE'],
    ['disable_version_sync', 'TRUE'],  // THIS is the key setting!
    ['cache_control_header', 'public, max-age=31536000'],
];

echo "Step 1: Setting S3FS configuration values\n";
foreach ($config_commands as list($key, $value)) {
    echo "  Setting $key = $value\n";
    passthru("./vendor/bin/drush config:set s3fs.settings $key '$value' -y 2>&1");
}
echo "\n";

echo "Step 2: Setting credentials (separately, as they use different method)\n";
$access_key = getenv('R2_ACCESS_KEY_ID');
$secret_key = getenv('R2_SECRET_ACCESS_KEY');
if ($access_key && $secret_key) {
    passthru("./vendor/bin/drush config:set s3fs.settings access_key '$access_key' -y 2>&1");
    passthru("./vendor/bin/drush config:set s3fs.settings secret_key '$secret_key' -y 2>&1");
    echo "✓ Credentials set\n\n";
}

echo "Step 3: Verify configuration\n";
passthru('./vendor/bin/drush config:get s3fs.settings 2>&1 | grep -E "(bucket|hostname|domain|disable_version)"');
echo "\n\n";

echo "Step 4: Clear cache\n";
passthru('./vendor/bin/drush cr 2>&1');
echo "\n\n";

echo "Step 5: Refresh S3FS file cache (this may take 30-60 seconds)\n";
passthru('./vendor/bin/drush s3fs-refresh-cache -y 2>&1', $ret);
echo "\nExit code: $ret\n\n";

if ($ret === 0) {
    echo "Step 6: Check cached file count\n";
    passthru('./vendor/bin/drush sqlq "SELECT COUNT(*) as file_count FROM s3fs_file" 2>&1');
    echo "\n";
    
    echo "Step 7: Show sample cached files\n";
    passthru('./vendor/bin/drush sqlq "SELECT uri FROM s3fs_file LIMIT 5" 2>&1');
    echo "\n\n";
    
    echo "✓✓✓ SUCCESS! Images should now load from Cloudflare R2!\n";
    echo "Visit: https://storyfulls-1.onrender.com\n";
} else {
    echo "✗ Still failing. This might be a deeper S3FS/R2 compatibility issue.\n";
}

echo "\n=== Done ===\n";
