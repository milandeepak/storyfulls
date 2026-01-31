<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();
$container->get('request_stack')->push(Request::createFromGlobals());

header('Content-Type: text/plain; charset=utf-8');

echo "=== Stream Wrapper Registration Diagnostic ===\n\n";

// Check Settings values (not Config)
echo "1. Settings values (from \$settings):\n";
$settings = \Drupal\Core\Site\Settings::getAll();
echo "   s3fs.use_s3_for_public: " . (isset($settings['s3fs.use_s3_for_public']) ? ($settings['s3fs.use_s3_for_public'] ? 'TRUE' : 'FALSE') : 'NOT SET') . "\n";
echo "   s3fs.use_s3_for_private: " . (isset($settings['s3fs.use_s3_for_private']) ? ($settings['s3fs.use_s3_for_private'] ? 'TRUE' : 'FALSE') : 'NOT SET') . "\n";
echo "   s3fs.bucket: " . ($settings['s3fs.bucket'] ?? 'NOT SET') . "\n";
echo "\n";

// Check stream wrapper manager
echo "2. Registered Stream Wrappers:\n";
$wrapper_manager = \Drupal::service('stream_wrapper_manager');
$wrappers = $wrapper_manager->getWrappers();
foreach (['public', 'private', 's3'] as $scheme) {
    if (isset($wrappers[$scheme])) {
        $info = $wrappers[$scheme];
        echo "   $scheme:// -> " . $info['class'] . "\n";
    } else {
        echo "   $scheme:// -> NOT REGISTERED\n";
    }
}
echo "\n";

// Test URL generation
echo "3. URL Generation Tests:\n";
$test_uris = [
    'public://book-covers/1.jpg',
    'public://book_covers/book_1611.jpg',
];
foreach ($test_uris as $uri) {
    try {
        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
        echo "   $uri\n";
        echo "   -> $url\n";
        
        // Check if it looks like an R2 URL
        if (strpos($url, 'r2.dev') !== false || strpos($url, 'cloudflarestorage.com') !== false) {
            echo "   ✓ Using R2!\n";
        } else {
            echo "   ✗ Still using local path\n";
        }
    } catch (Exception $e) {
        echo "   $uri -> ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Check if file exists via stream wrapper
echo "4. File Existence Test via Stream Wrapper:\n";
$test_uri = 'public://book-covers/1.jpg';
echo "   Testing: $test_uri\n";
if (file_exists($test_uri)) {
    echo "   ✓ File exists via stream wrapper\n";
    $size = filesize($test_uri);
    echo "   Size: $size bytes\n";
} else {
    echo "   ✗ File does NOT exist via stream wrapper\n";
}
echo "\n";

echo "=== END DIAGNOSTIC ===\n";
