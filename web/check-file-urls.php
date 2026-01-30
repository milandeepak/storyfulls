<?php
/**
 * Check actual file URLs being generated
 * Access at: https://storyfulls-1.onrender.com/check-file-urls.php
 */

header('Content-Type: text/plain');

echo "=== File URL Diagnostic ===\n\n";

// Bootstrap Drupal
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once __DIR__ . '/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->prepareLegacyRequest($request);

echo "✓ Drupal bootstrapped\n\n";

// Check S3FS settings
$config = \Drupal::config('s3fs.settings');
echo "=== S3FS Configuration ===\n";
echo "use_s3_for_public: " . ($config->get('use_s3_for_public') ? 'TRUE' : 'FALSE') . "\n";
echo "bucket: " . $config->get('bucket') . "\n";
echo "domain: " . $config->get('domain') . "\n";
echo "use_cname: " . ($config->get('use_cname') ? 'TRUE' : 'FALSE') . "\n";
echo "root_folder: " . $config->get('root_folder') . "\n\n";

// Get some actual book cover files from database
$database = \Drupal::database();

echo "=== Sample Book Cover Files ===\n";
$query = $database->query("
  SELECT fm.uri, fm.filename, fm.fid
  FROM file_managed fm
  WHERE fm.uri LIKE 'public://book-covers/%'
  LIMIT 5
");

foreach ($query as $file) {
    echo "\nFile ID: {$file->fid}\n";
    echo "  Filename: {$file->filename}\n";
    echo "  URI: {$file->uri}\n";
    
    // Generate URL using Drupal's file_create_url equivalent
    $file_entity = \Drupal\file\Entity\File::load($file->fid);
    if ($file_entity) {
        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->uri);
        echo "  Generated URL: {$url}\n";
        
        // Check if file exists in S3FS cache
        $s3fs_check = $database->query("SELECT filesize FROM s3fs_file WHERE uri = :uri", [':uri' => $file->uri])->fetchField();
        echo "  In S3FS cache: " . ($s3fs_check ? "YES (size: $s3fs_check bytes)" : "NO") . "\n";
    }
}

echo "\n=== S3FS Cache Statistics ===\n";
$total = $database->query("SELECT COUNT(*) FROM s3fs_file")->fetchField();
echo "Total files in cache: {$total}\n";

$book_covers = $database->query("SELECT COUNT(*) FROM s3fs_file WHERE uri LIKE 'public://book-covers/%'")->fetchField();
echo "Book covers in cache: {$book_covers}\n";

// Show some URIs from S3FS cache
echo "\nSample URIs from S3FS cache:\n";
$sample = $database->query("SELECT uri FROM s3fs_file WHERE uri LIKE 'public://book-covers/%' LIMIT 5");
foreach ($sample as $row) {
    echo "  - {$row->uri}\n";
}

echo "\n=== Done ===\n";
