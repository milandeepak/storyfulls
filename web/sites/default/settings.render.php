<?php

/**
 * @file
 * Render.com environment settings.
 *
 * Loaded on Render. Configures database, trusted hosts, and base URL for HTTPS.
 *
 * Supported DB config:
 * - MySQL (recommended for this project on Render): DB_HOST, DB_PORT, DB_NAME,
 *   DB_USER, DB_PASS (set via Render env vars)
 * - DATABASE_URL: mysql://... or postgresql://... (fallback)
 */

// --- Database ---------------------------------------------------------------
// Supports MySQL/MariaDB and TiDB Cloud (MySQL-compatible with SSL).
// Set these env vars in Render:
// - DB_HOST: Database hostname
// - DB_PORT: Database port (default: 3306 for MySQL, 4000 for TiDB)
// - DB_NAME: Database name
// - DB_USER: Database username
// - DB_PASS: Database password
// - DB_USE_SSL: Set to "true" for TiDB Cloud (optional for standard MySQL)

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_port = getenv('DB_PORT') ?: '3306';
$db_use_ssl = getenv('DB_USE_SSL');

if (!empty($db_host) && !empty($db_name) && !empty($db_user)) {
  $databases['default']['default'] = [
    'driver' => 'mysql',
    'database' => $db_name,
    'username' => $db_user,
    'password' => $db_pass ?: '',
    'host' => $db_host,
    'port' => $db_port,
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
  ];

  // TiDB Cloud requires SSL/TLS connection
  if ($db_use_ssl === 'true' || $db_use_ssl === '1') {
    $databases['default']['default']['pdo'] = [
      PDO::MYSQL_ATTR_SSL_CA => TRUE,
      PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => FALSE,
    ];
  }
}
else {
  // Fallback: DATABASE_URL (postgresql://... or mysql://...)
  $database_url = getenv('DATABASE_URL');
  if (!empty($database_url)) {
    $url = parse_url($database_url);
    if (isset($url['scheme'], $url['host'], $url['path'])) {
      $scheme = strtolower($url['scheme']);
      if ($scheme === 'postgres' || $scheme === 'postgresql') {
        $databases['default']['default'] = [
          'driver'   => 'pgsql',
          'database' => ltrim($url['path'] ?? '', '/'),
          'username' => $url['user'] ?? '',
          'password' => $url['pass'] ?? '',
          'host'     => $url['host'] ?? 'localhost',
          'port'     => $url['port'] ?? '5432',
          'prefix'   => '',
        ];
      }
      elseif ($scheme === 'mysql' || $scheme === 'mariadb') {
        $databases['default']['default'] = [
          'driver' => 'mysql',
          'database' => ltrim($url['path'] ?? '', '/'),
          'username' => $url['user'] ?? '',
          'password' => $url['pass'] ?? '',
          'host' => $url['host'] ?? 'localhost',
          'port' => $url['port'] ?? '3306',
          'prefix' => '',
          'collation' => 'utf8mb4_general_ci',
        ];
      }
    }
  }
}

// Trusted host: allow Render URL and optional custom domain
// Note: These are regex patterns (no delimiters).
$settings['trusted_host_patterns'] = [
  '^(.+\\.)?onrender\\.com$',
];
if ($custom = getenv('TRUSTED_HOST_PATTERNS')) {
  // Comma-separated list of regex patterns.
  foreach (array_filter(array_map('trim', explode(',', $custom))) as $pattern) {
    $settings['trusted_host_patterns'][] = $pattern;
  }
}

// Hash salt from env (generate with: openssl rand -hex 32)
// CRITICAL: Hash salt is required for Drupal to work. If not set via env var,
// generate one automatically (though it will change on each deploy without persistence)
if ($salt = getenv('DRUPAL_HASH_SALT')) {
  $settings['hash_salt'] = $salt;
}
else {
  // Fallback: generate from app name or use a static default
  // WARNING: Use DRUPAL_HASH_SALT env var in production for stability
  $settings['hash_salt'] = hash('sha256', 'storyfulls-render-' . ($db_host ?: 'fallback'));
}

// Base URL for HTTPS (avoids mixed content for links and file URLs)
if ($base_url = getenv('DRUPAL_BASE_URL')) {
  $base_url = rtrim($base_url, '/');
  $GLOBALS['base_url'] = $base_url . '/';
  $settings['file_public_base_url'] = $base_url . '/sites/default/files';
}

// Config sync directory (writable location)
$settings['config_sync_directory'] = '/tmp/drupal-sync';

// Temp directory (must be writable on Render)
$settings['file_temp_path'] = '/tmp';

// Skip permissions hardening in container
$settings['skip_permissions_hardening'] = TRUE;

// File paths - use /tmp for private files since filesystem is ephemeral
$settings['file_private_path'] = '/tmp/private';

// --- S3FS / Cloudflare R2 Configuration -------------------------------------
// DISABLED: Now using local file storage and committing files to GitHub
// S3FS has been removed in favor of standard Drupal file system

// Ensure S3FS is NOT used for public/private files
$settings['s3fs.use_s3_for_public'] = FALSE;
$settings['s3fs.use_s3_for_private'] = FALSE;
