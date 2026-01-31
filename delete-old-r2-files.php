#!/usr/bin/env php
<?php
/**
 * Delete old R2 files that are NOT in s3fs-public structure
 * Run this AFTER verifying the new structure works correctly
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

echo "=== Deleting Old R2 Files (NOT in s3fs-public) ===\n\n";
echo "Bucket: $bucket\n";
echo "IMPORTANT: This will delete files in drupal-files/* that are NOT in drupal-files/s3fs-public/\n\n";

// Ask for confirmation
echo "Type 'DELETE' to confirm deletion: ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
if ($line !== 'DELETE') {
    echo "Cancelled.\n";
    exit(0);
}

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

echo "\nListing files under drupal-files/...\n";

try {
    $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => 'drupal-files/',
    ]);
    
    $files = $objects['Contents'] ?? [];
    echo "Found " . count($files) . " total files\n\n";
    
    $filesToDelete = [];
    $filesToKeep = [];
    
    foreach ($files as $file) {
        $key = $file['Key'];
        
        // Keep files in s3fs-public structure
        if (strpos($key, 'drupal-files/s3fs-public/') === 0) {
            $filesToKeep[] = $key;
            continue;
        }
        
        // Keep the drupal-files/ folder itself
        if ($key === 'drupal-files/' || $key === 'drupal-files') {
            $filesToKeep[] = $key;
            continue;
        }
        
        // Mark for deletion
        $filesToDelete[] = [
            'Key' => $key,
        ];
    }
    
    echo "Files to keep (in s3fs-public/): " . count($filesToKeep) . "\n";
    echo "Files to DELETE (old structure): " . count($filesToDelete) . "\n\n";
    
    if (empty($filesToDelete)) {
        echo "No files need to be deleted!\n";
        exit(0);
    }
    
    echo "Deleting " . count($filesToDelete) . " files...\n";
    
    // Delete in batches of 1000 (AWS limit)
    $batches = array_chunk($filesToDelete, 1000);
    $totalDeleted = 0;
    
    foreach ($batches as $i => $batch) {
        echo "Batch " . ($i + 1) . "/" . count($batches) . " (" . count($batch) . " files)...\n";
        
        try {
            $result = $s3->deleteObjects([
                'Bucket' => $bucket,
                'Delete' => [
                    'Objects' => $batch,
                    'Quiet' => false,
                ],
            ]);
            
            $deleted = $result['Deleted'] ?? [];
            $errors = $result['Errors'] ?? [];
            
            $totalDeleted += count($deleted);
            
            if (!empty($errors)) {
                echo "  Errors: " . count($errors) . "\n";
                foreach ($errors as $error) {
                    echo "    {$error['Key']}: {$error['Message']}\n";
                }
            }
        } catch (AwsException $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Successfully deleted: $totalDeleted files\n";
    echo "Remaining files (in s3fs-public/): " . count($filesToKeep) . "\n\n";
    echo "Next steps:\n";
    echo "1. Visit: https://storyfulls-1.onrender.com/manual-rebuild-cache.php\n";
    echo "2. Verify images load from R2\n";
    
} catch (AwsException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
