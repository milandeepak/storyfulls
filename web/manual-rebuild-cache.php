<?php
/**
 * Manually rebuild S3FS cache - run this via web browser
 */

echo "=== Manual S3FS Cache Rebuild ===\n\n";

echo "Step 1: Truncating S3FS cache...\n";
passthru("vendor/bin/drush sqlq 'TRUNCATE TABLE s3fs_file' 2>&1", $ret1);
echo "Return code: $ret1\n\n";

echo "Step 2: Refreshing S3FS cache from R2...\n";
passthru("vendor/bin/drush s3fs-refresh-cache -y 2>&1", $ret2);
echo "Return code: $ret2\n\n";

echo "Step 3: Checking cache...\n";
passthru("vendor/bin/drush sqlq \"SELECT COUNT(*) FROM s3fs_file\" 2>&1");
echo "\n\n";

echo "Step 4: Checking public:// URIs...\n";
passthru("vendor/bin/drush sqlq \"SELECT COUNT(*) FROM s3fs_file WHERE uri LIKE 'public://%'\" 2>&1");
echo "\n\n";

echo "Step 5: Sample public:// book cover URIs...\n";
passthru("vendor/bin/drush sqlq \"SELECT uri FROM s3fs_file WHERE uri LIKE 'public://book%' LIMIT 10\" 2>&1");
echo "\n\n";

echo "Step 6: Clearing Drupal cache...\n";
passthru("vendor/bin/drush cr 2>&1", $ret3);
echo "Return code: $ret3\n\n";

echo "=== Rebuild Complete ===\n";
echo "Check the site now!\n";
