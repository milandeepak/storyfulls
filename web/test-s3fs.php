<?php
/**
 * S3FS and file system diagnostic test for Render deployment.
 * Access this at: https://storyfulls-1.onrender.com/test-s3fs.php
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

echo "<h1>S3FS Configuration Test</h1>\n";
echo "<pre>\n";

// Check if Drupal can bootstrap
$autoloader = require_once __DIR__ . '/autoload.php';

try {
  $request = Request::createFromGlobals();
  $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();
  $kernel->prepareLegacyRequest($request);
  
  echo "✓ Drupal bootstrapped successfully\n\n";
  
  // Check if S3FS module is installed
  $module_handler = \Drupal::service('module_handler');
  $s3fs_installed = $module_handler->moduleExists('s3fs');
  
  echo "=== S3FS Module Status ===\n";
  echo "S3FS module installed: " . ($s3fs_installed ? 'YES' : 'NO') . "\n";
  
  if ($s3fs_installed) {
    echo "✓ S3FS module is enabled\n\n";
    
    // Get S3FS configuration
    echo "=== S3FS Configuration ===\n";
    $config = \Drupal::config('s3fs.settings');
    
    echo "use_s3_for_public: " . ($config->get('use_s3_for_public') ? 'TRUE' : 'FALSE') . "\n";
    echo "use_s3_for_private: " . ($config->get('use_s3_for_private') ? 'TRUE' : 'FALSE') . "\n";
    echo "bucket: " . ($config->get('bucket') ?: 'NOT SET') . "\n";
    echo "region: " . ($config->get('region') ?: 'NOT SET') . "\n";
    echo "use_customhost: " . ($config->get('use_customhost') ? 'TRUE' : 'FALSE') . "\n";
    echo "hostname: " . ($config->get('hostname') ?: 'NOT SET') . "\n";
    echo "use_cname: " . ($config->get('use_cname') ? 'TRUE' : 'FALSE') . "\n";
    echo "domain: " . ($config->get('domain') ?: 'NOT SET') . "\n";
    echo "root_folder: " . ($config->get('root_folder') ?: 'NOT SET') . "\n";
    echo "use_path_style_endpoint: " . ($config->get('use_path_style_endpoint') ? 'TRUE' : 'FALSE') . "\n";
    
    // Check if credentials are set
    echo "\naccess_key: " . ($config->get('access_key') ? '***SET***' : 'NOT SET') . "\n";
    echo "secret_key: " . ($config->get('secret_key') ? '***SET***' : 'NOT SET') . "\n";
    
    echo "\n=== S3FS Cache Status ===\n";
    
    // Check if S3FS cache has been populated
    $database = \Drupal::database();
    try {
      $count = $database->query("SELECT COUNT(*) FROM {s3fs_file}")->fetchField();
      echo "Files in s3fs_file table: " . $count . "\n";
      
      if ($count == 0) {
        echo "⚠ WARNING: S3FS file cache is empty. Run 'drush s3fs-refresh-cache' to populate it.\n";
      } else {
        // Show sample files
        echo "\nSample files from S3FS cache:\n";
        $results = $database->query("SELECT uri, filesize FROM {s3fs_file} LIMIT 5")->fetchAll();
        foreach ($results as $row) {
          echo "  - {$row->uri} (" . round($row->filesize/1024, 2) . " KB)\n";
        }
      }
    } catch (\Exception $e) {
      echo "✗ ERROR: Could not query s3fs_file table: " . $e->getMessage() . "\n";
    }
    
  } else {
    echo "✗ S3FS module is NOT enabled\n";
    echo "\nTo enable S3FS, run:\n";
    echo "  drush pm:enable s3fs -y\n";
  }
  
  echo "\n=== File System Configuration ===\n";
  $file_public_path = \Drupal::service('file_system')->realpath('public://');
  $file_private_path = \Drupal::service('file_system')->realpath('private://');
  
  echo "Public file path: " . ($file_public_path ?: 'NOT SET') . "\n";
  echo "Private file path: " . ($file_private_path ?: 'NOT SET') . "\n";
  
  // Test generating a file URL
  echo "\n=== Test File URL Generation ===\n";
  $test_uri = 'public://book_covers/test.jpg';
  $test_url = \Drupal\Core\Url::fromUri(file_create_url($test_uri))->toString();
  echo "Test URI: {$test_uri}\n";
  echo "Generated URL: {$test_url}\n";
  
  // Check actual book images
  echo "\n=== Sample Book Cover Files ===\n";
  try {
    $query = $database->query("SELECT nid, title FROM node_field_data WHERE type='book' LIMIT 5");
    $books = $query->fetchAll();
    
    foreach ($books as $book) {
      echo "\nBook: {$book->title} (nid:{$book->nid})\n";
      
      // Get field_cover_image value
      $cover_query = $database->query("SELECT field_cover_image_target_id FROM node__field_cover_image WHERE entity_id = :nid", [':nid' => $book->nid]);
      $cover_fid = $cover_query->fetchField();
      
      if ($cover_fid) {
        $file_query = $database->query("SELECT uri FROM file_managed WHERE fid = :fid", [':fid' => $cover_fid]);
        $file_uri = $file_query->fetchField();
        
        if ($file_uri) {
          $file_url = file_create_url($file_uri);
          echo "  Cover URI: {$file_uri}\n";
          echo "  Cover URL: {$file_url}\n";
        }
      } else {
        echo "  No cover image\n";
      }
    }
  } catch (\Exception $e) {
    echo "Error querying books: " . $e->getMessage() . "\n";
  }
  
} catch (\Exception $e) {
  echo "✗ ERROR bootstrapping Drupal: " . $e->getMessage() . "\n";
  echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Environment Variables ===\n";
echo "R2_BUCKET: " . (getenv('R2_BUCKET') ?: 'NOT SET') . "\n";
echo "R2_ENDPOINT: " . (getenv('R2_ENDPOINT') ?: 'NOT SET') . "\n";
echo "R2_PUBLIC_URL: " . (getenv('R2_PUBLIC_URL') ?: 'NOT SET') . "\n";
echo "R2_ACCESS_KEY_ID: " . (getenv('R2_ACCESS_KEY_ID') ? '***SET***' : 'NOT SET') . "\n";
echo "R2_SECRET_ACCESS_KEY: " . (getenv('R2_SECRET_ACCESS_KEY') ? '***SET***' : 'NOT SET') . "\n";

echo "</pre>\n";
