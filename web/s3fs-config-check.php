<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();
$container->get('request_stack')->push(Request::createFromGlobals());

header('Content-Type: text/plain; charset=utf-8');

echo "=== S3FS Configuration Diagnostic ===\n\n";

// Get S3FS configuration
$config = \Drupal::config('s3fs.settings');

echo "Configuration values:\n";
echo "- bucket: '" . $config->get('bucket') . "'\n";
echo "- region: '" . $config->get('region') . "'\n";
echo "- use_customhost: " . ($config->get('use_customhost') ? 'TRUE' : 'FALSE') . "\n";
echo "- hostname: '" . $config->get('hostname') . "'\n";
echo "- use_cname: " . ($config->get('use_cname') ? 'TRUE' : 'FALSE') . "\n";
echo "- domain: '" . $config->get('domain') . "'\n";
echo "- root_folder: '" . $config->get('root_folder') . "'\n";
echo "- public_folder: '" . $config->get('public_folder') . "'\n";
echo "- private_folder: '" . $config->get('private_folder') . "'\n";
echo "- use_s3_for_public: " . ($config->get('use_s3_for_public') ? 'TRUE' : 'FALSE') . "\n";
echo "- use_s3_for_private: " . ($config->get('use_s3_for_private') ? 'TRUE' : 'FALSE') . "\n";
echo "- use_versioning: " . ($config->get('use_versioning') ? 'TRUE' : 'FALSE') . "\n";
echo "\n";

// Check cache entries
$database = \Drupal::database();
echo "Cache sample (10 entries):\n";
$query = $database->query("SELECT uri, filesize FROM s3fs_file LIMIT 10");
$results = $query->fetchAll();
foreach ($results as $row) {
    echo "  {$row->uri} ({$row->filesize} bytes)\n";
}
echo "\n";

// Check specifically for book covers with different schemes
echo "Book cover URIs in cache:\n";
$query = $database->query("SELECT uri FROM s3fs_file WHERE uri LIKE '%book-cover%' LIMIT 5");
$results = $query->fetchAll();
if (empty($results)) {
    echo "  No entries found with 'book-cover' in URI\n";
}
foreach ($results as $row) {
    echo "  {$row->uri}\n";
}
echo "\n";

// Check environment variables
echo "Environment variables:\n";
echo "- R2_BUCKET: '" . getenv('R2_BUCKET') . "'\n";
echo "- R2_ENDPOINT: '" . getenv('R2_ENDPOINT') . "'\n";
echo "- R2_PUBLIC_URL: '" . getenv('R2_PUBLIC_URL') . "'\n";
echo "\n";

// Test file URL generation
echo "URL generation test:\n";
$test_uris = [
    'public://book-covers/1.jpg',
    's3://book-covers/1.jpg',
];
foreach ($test_uris as $uri) {
    try {
        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
        echo "  $uri -> $url\n";
    } catch (Exception $e) {
        echo "  $uri -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== END DIAGNOSTIC ===\n";
