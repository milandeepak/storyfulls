#!/usr/bin/env php
<?php
/**
 * Reorganize R2 files to match S3FS expected structure
 * 
 * Current structure: drupal-files/book-covers/1.jpg
 * Expected structure: drupal-files/s3fs-public/book-covers/1.jpg
 * 
 * This script will:
 * 1. List all files in drupal-files/
 * 2. Copy them to drupal-files/s3fs-public/
 * 3. Optionally delete originals after verification
 */

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Load environment variables
$bucket = getenv('R2_BUCKET');
$endpoint = getenv('R2_ENDPOINT');
$accessKey = getenv('R2_ACCESS_KEY_ID');
$secretKey = getenv('R2_SECRET_ACCESS_KEY');

if (empty($bucket) || empty($endpoint) || empty($accessKey) || empty($secretKey)) {
    echo "ERROR: R2 environment variables not set!\n";
    exit(1);
}

echo "=== Reorganizing R2 Files for S3FS ===\n\n";
echo "Bucket: $bucket\n";
echo "Endpoint: $endpoint\n\n";

// Create S3 client
$s3 = new S3Client([
    'version' => 'latest',
    'region' => 'auto',
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => false,
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ],
]);

echo "Step 1: Listing files under drupal-files/\n";

try {
    $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => 'drupal-files/',
    ]);
    
    $files = $objects['Contents'] ?? [];
    echo "Found " . count($files) . " files\n\n";
    
    $filesToCopy = [];
    $skippedFiles = [];
    
    foreach ($files as $file) {
        $key = $file['Key'];
        
        // Skip if already in s3fs-public structure
        if (strpos($key, 'drupal-files/s3fs-public/') === 0) {
            $skippedFiles[] = $key;
            continue;
        }
        
        // Skip if it's the drupal-files/ folder itself
        if ($key === 'drupal-files/' || $key === 'drupal-files') {
            continue;
        }
        
        // Calculate new key: drupal-files/book-covers/1.jpg -> drupal-files/s3fs-public/book-covers/1.jpg
        $relativePath = substr($key, strlen('drupal-files/'));
        $newKey = 'drupal-files/s3fs-public/' . $relativePath;
        
        $filesToCopy[] = [
            'old' => $key,
            'new' => $newKey,
            'size' => $file['Size'],
        ];
    }
    
    echo "Files already in s3fs-public/: " . count($skippedFiles) . "\n";
    echo "Files to copy: " . count($filesToCopy) . "\n\n";
    
    if (empty($filesToCopy)) {
        echo "No files need to be reorganized!\n";
        exit(0);
    }
    
    echo "Step 2: Copying files to new structure\n";
    echo "This will NOT delete the originals yet.\n\n";
    
    $copied = 0;
    $errors = 0;
    
    foreach ($filesToCopy as $file) {
        echo "Copying: {$file['old']} -> {$file['new']}\n";
        
        try {
            // Copy object within bucket
            $s3->copyObject([
                'Bucket' => $bucket,
                'Key' => $file['new'],
                'CopySource' => "$bucket/{$file['old']}",
            ]);
            $copied++;
        } catch (AwsException $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Successfully copied: $copied files\n";
    echo "Errors: $errors\n";
    echo "\nOriginal files were NOT deleted. To delete them, run:\n";
    echo "php web/cleanup-old-r2-files.php\n\n";
    echo "Next steps:\n";
    echo "1. Remove public_folder config override from settings.render.php\n";
    echo "2. Run: drush sqlq 'TRUNCATE TABLE s3fs_file'\n";
    echo "3. Run: drush s3fs-refresh-cache -y\n";
    echo "4. Verify images load correctly\n";
    echo "5. Then delete old files with cleanup script\n";
    
} catch (AwsException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
