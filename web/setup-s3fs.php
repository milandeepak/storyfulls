<?php
/**
 * One-time S3FS setup script.
 * Access once at: https://storyfulls-1.onrender.com/setup-s3fs.php
 * Then delete this file for security.
 */

// Simple output function
function output($message) {
  echo $message . "\n";
  flush();
  ob_flush();
}

// Start output buffering
ob_start();
echo "<html><head><title>S3FS Setup</title></head><body><pre>\n";
echo "=== S3FS One-Time Setup ===\n\n";
flush();
ob_flush();

chdir(__DIR__ . '/..');

// Enable S3FS module
output("Step 1: Enabling S3FS module...");
exec('./vendor/bin/drush pm:enable s3fs -y 2>&1', $output1, $return1);
output(implode("\n", $output1));
output($return1 === 0 ? "✓ Success" : "⚠ Warning: exit code $return1");
output("");

// Check module status
output("Step 2: Checking module status...");
exec('./vendor/bin/drush pm:list --status=enabled --type=module 2>&1 | grep s3fs', $output2, $return2);
output(implode("\n", $output2));
output("");

// Refresh S3FS cache
output("Step 3: Refreshing S3FS file cache (this may take a minute)...");
exec('./vendor/bin/drush s3fs-refresh-cache -y 2>&1', $output3, $return3);
output(implode("\n", $output3));
output($return3 === 0 ? "✓ Success" : "⚠ Warning: exit code $return3");
output("");

// Check cache status
output("Step 4: Checking S3FS cache status...");
exec('./vendor/bin/drush sqlq "SELECT COUNT(*) FROM s3fs_file" 2>&1', $output4, $return4);
output("Files in S3FS cache: " . trim(implode("\n", $output4)));
output("");

// Show sample file URLs
output("Step 5: Testing file URL generation...");
exec('./vendor/bin/drush php:eval "echo file_create_url(\'public://book-covers/1.jpg\');" 2>&1', $output5, $return5);
output("Sample book cover URL: " . trim(implode("\n", $output5)));
output("");

// Clear cache
output("Step 6: Clearing Drupal cache...");
exec('./vendor/bin/drush cr 2>&1', $output6, $return6);
output($return6 === 0 ? "✓ Cache cleared" : "⚠ Warning: exit code $return6");
output("");

output("=== Setup Complete! ===");
output("");
output("Next steps:");
output("1. Visit your site homepage: https://storyfulls-1.onrender.com");
output("2. Check if book images are loading");
output("3. Delete this file (setup-s3fs.php) for security");

echo "</pre></body></html>";
ob_end_flush();
