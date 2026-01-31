<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();
$container->get('request_stack')->push(Request::createFromGlobals());

echo "=== S3FS Stream Wrapper Diagnostic ===\n\n";

// Check stream wrapper manager
echo "1. Registered Stream Wrappers:\n";
$wrapper_manager = \Drupal::service('stream_wrapper_manager');
$wrappers = $wrapper_manager->getWrappers();
foreach ($wrappers as $scheme => $info) {
    echo "   $scheme:// -> " . $info['class'] . "\n";
    if ($scheme === 'public') {
        echo "      ** PUBLIC scheme is handled by: " . $info['class'] . " **\n";
    }
}
echo "\n";

// Check S3FS configuration
echo "2. S3FS Configuration:\n";
$config = \Drupal::config('s3fs.settings');
echo "   use_s3_for_public: " . ($config->get('use_s3_for_public') ? 'TRUE' : 'FALSE') . "\n";
echo "   use_s3_for_private: " . ($config->get('use_s3_for_private') ? 'TRUE' : 'FALSE') . "\n";
echo "   bucket: " . $config->get('bucket') . "\n";
echo "   region: " . $config->get('region') . "\n";
echo "   use_customhost: " . ($config->get('use_customhost') ? 'TRUE' : 'FALSE') . "\n";
echo "   hostname: " . $config->get('hostname') . "\n";
echo "   use_cname: " . ($config->get('use_cname') ? 'TRUE' : 'FALSE') . "\n";
echo "   domain: " . $config->get('domain') . "\n";
echo "   root_folder: " . $config->get('root_folder') . "\n";
echo "\n";

// Check S3FS cache sample
echo "3. S3FS Cache Sample (first 10 book covers):\n";
$database = \Drupal::database();
$query = $database->query("SELECT uri, filesize FROM s3fs_file WHERE uri LIKE '%book%cover%' LIMIT 10");
$results = $query->fetchAll();
if (empty($results)) {
    echo "   NO RESULTS! Cache might be empty.\n";
} else {
    foreach ($results as $row) {
        echo "   URI: {$row->uri} (Size: {$row->filesize})\n";
    }
}
echo "\n";

// Check file_managed sample
echo "4. File Managed Sample (first 10 book covers):\n";
$query = $database->query("SELECT uri, filename FROM file_managed WHERE uri LIKE '%book%cover%' LIMIT 10");
$results = $query->fetchAll();
foreach ($results as $row) {
    echo "   URI: {$row->uri} (Filename: {$row->filename})\n";
}
echo "\n";

// Test public:// URL generation
echo "5. Testing public:// URL Generation:\n";
$test_uri = 'public://book-covers/1.jpg';
try {
    $url = \Drupal::service('file_url_generator')->generateAbsoluteString($test_uri);
    echo "   URI: $test_uri\n";
    echo "   Generated URL: $url\n";
    echo "   ** Should be R2 URL if S3FS is working! **\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Check if S3FS module is enabled
echo "6. S3FS Module Status:\n";
$moduleHandler = \Drupal::service('module_handler');
echo "   s3fs module enabled: " . ($moduleHandler->moduleExists('s3fs') ? 'YES' : 'NO') . "\n";
echo "\n";

echo "=== END DIAGNOSTIC ===\n";
