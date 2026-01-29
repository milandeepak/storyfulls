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
// Prefer explicit MySQL env vars (works well with Render private MySQL service).
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_port = getenv('DB_PORT') ?: '3306';

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
if ($salt = getenv('DRUPAL_HASH_SALT')) {
  $settings['hash_salt'] = $salt;
}

// Base URL for HTTPS (avoids mixed content for links and file URLs)
if ($base_url = getenv('DRUPAL_BASE_URL')) {
  $base_url = rtrim($base_url, '/');
  $GLOBALS['base_url'] = $base_url . '/';
  $settings['file_public_base_url'] = $base_url . '/sites/default/files';
}

// Config sync directory
$settings['config_sync_directory'] = 'sites/default/files/sync';

// Skip permissions hardening in container
$settings['skip_permissions_hardening'] = TRUE;
