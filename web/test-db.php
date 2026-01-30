<?php
/**
 * Database connection test for Render deployment.
 * Access this at: https://storyfulls-1.onrender.com/test-db.php
 */

echo "<h1>Render Environment Test</h1>\n";
echo "<pre>\n";

// Check if RENDER env var is set
echo "RENDER env var: " . (getenv('RENDER') ? 'SET' : 'NOT SET') . "\n";
echo "\n";

// Check database environment variables
echo "=== Database Configuration ===\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: 'NOT SET') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '***SET***' : 'NOT SET') . "\n";
echo "DB_USE_SSL: " . (getenv('DB_USE_SSL') ?: 'NOT SET') . "\n";
echo "\n";

// Check other critical env vars
echo "=== Other Configuration ===\n";
echo "DRUPAL_HASH_SALT: " . (getenv('DRUPAL_HASH_SALT') ? '***SET***' : 'NOT SET') . "\n";
echo "DRUPAL_BASE_URL: " . (getenv('DRUPAL_BASE_URL') ?: 'NOT SET') . "\n";
echo "\n";

// Test database connection
echo "=== Database Connection Test ===\n";
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_use_ssl = getenv('DB_USE_SSL');

if (!empty($db_host) && !empty($db_name) && !empty($db_user)) {
  try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name}";
    
    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    
    if ($db_use_ssl === 'true' || $db_use_ssl === '1') {
      $options[PDO::MYSQL_ATTR_SSL_CA] = TRUE;
      $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = FALSE;
      echo "SSL/TLS: ENABLED\n";
    } else {
      echo "SSL/TLS: DISABLED\n";
    }
    
    echo "Connecting to: {$dsn}\n";
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "✓ Database connection: SUCCESS\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM node_field_data");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Found {$result['count']} nodes in database\n";
    
    // Check key_value table
    $stmt = $pdo->query("SELECT value FROM key_value WHERE collection='state' AND name='install_task'");
    $install_task = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($install_task) {
      echo "✓ install_task state: " . $install_task['value'] . "\n";
    } else {
      echo "✗ install_task state: NOT FOUND\n";
    }
    
    // Check config table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM config");
    $config_count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Found {$config_count['count']} config entries\n";
    
  } catch (PDOException $e) {
    echo "✗ Database connection FAILED: " . $e->getMessage() . "\n";
  }
} else {
  echo "✗ Database environment variables not properly set\n";
}

echo "\n=== File Paths ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "settings.php exists: " . (file_exists(__DIR__ . '/sites/default/settings.php') ? 'YES' : 'NO') . "\n";
echo "settings.render.php exists: " . (file_exists(__DIR__ . '/sites/default/settings.render.php') ? 'YES' : 'NO') . "\n";
echo "settings.ddev.php exists: " . (file_exists(__DIR__ . '/sites/default/settings.ddev.php') ? 'YES' : 'NO') . "\n";

echo "\n=== Writable Directories ===\n";
$dirs_to_check = [
  '/tmp',
  '/tmp/drupal-sync',
  '/tmp/private',
  __DIR__ . '/sites/default/files'
];

foreach ($dirs_to_check as $dir) {
  $exists = is_dir($dir);
  $writable = $exists && is_writable($dir);
  echo "$dir: " . ($exists ? 'EXISTS' : 'MISSING') . ", " . ($writable ? 'WRITABLE' : 'NOT WRITABLE') . "\n";
}

echo "</pre>\n";
