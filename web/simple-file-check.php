<?php
/**
 * Simple database query to check file URLs - no Drupal bootstrap
 * Access at: https://storyfulls-1.onrender.com/simple-file-check.php
 */

header('Content-Type: text/plain');

echo "=== Simple File URL Check ===\n\n";

// Direct database connection
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => TRUE,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => FALSE,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "✓ Connected to database\n\n";
    
    // Check S3FS cache
    echo "=== S3FS Cache Status ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM s3fs_file");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total files in S3FS cache: {$total}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM s3fs_file WHERE uri LIKE 'public://book-covers/%'");
    $book_covers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Book covers in S3FS cache: {$book_covers}\n\n";
    
    // Sample files from S3FS cache
    echo "=== Sample Files from S3FS Cache ===\n";
    $stmt = $pdo->query("SELECT uri, filesize, timestamp FROM s3fs_file LIMIT 10");
    foreach ($stmt as $row) {
        echo "{$row['uri']} - {$row['filesize']} bytes\n";
    }
    
    echo "\n=== Sample Book Covers from file_managed ===\n";
    $stmt = $pdo->query("SELECT fid, uri, filename FROM file_managed WHERE uri LIKE 'public://book-covers/%' LIMIT 5");
    foreach ($stmt as $row) {
        echo "\nFile ID: {$row['fid']}\n";
        echo "  URI: {$row['uri']}\n";
        echo "  Filename: {$row['filename']}\n";
        
        // Check if in S3FS cache
        $check = $pdo->prepare("SELECT filesize FROM s3fs_file WHERE uri = ?");
        $check->execute([$row['uri']]);
        $result = $check->fetch(PDO::FETCH_ASSOC);
        echo "  In S3FS cache: " . ($result ? "YES ({$result['filesize']} bytes)" : "NO - MISSING!") . "\n";
    }
    
    // Check S3FS config from database
    echo "\n=== S3FS Configuration from Database ===\n";
    $stmt = $pdo->query("SELECT data FROM config WHERE name = 's3fs.settings'");
    $config_data = $stmt->fetch(PDO::FETCH_ASSOC)['data'];
    $config = unserialize($config_data);
    
    echo "use_s3_for_public: " . ($config['use_s3_for_public'] ? 'TRUE' : 'FALSE') . "\n";
    echo "bucket: " . $config['bucket'] . "\n";
    echo "hostname: " . $config['hostname'] . "\n";
    echo "domain: " . $config['domain'] . "\n";
    echo "use_cname: " . ($config['use_cname'] ? 'TRUE' : 'FALSE') . "\n";
    echo "root_folder: " . $config['root_folder'] . "\n";
    echo "disable_version_sync: " . ($config['disable_version_sync'] ? 'TRUE' : 'FALSE') . "\n";
    
    // Expected URL format
    echo "\n=== Expected URL Format ===\n";
    $domain = $config['domain'];
    $root_folder = $config['root_folder'];
    echo "Example book cover URL should be:\n";
    echo "{$domain}/{$root_folder}/book-covers/1.jpg\n";
    echo "\nWhich translates to:\n";
    echo "https://pub-421b25a0828946dda54e908e094bc6a2.r2.dev/drupal-files/book-covers/1.jpg\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
