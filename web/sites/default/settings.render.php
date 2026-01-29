<?php

/**
 * @file
 * Render.com environment settings.
 *
 * Loaded when DATABASE_URL is set (e.g. on Render). Parses DATABASE_URL
 * and configures database, trusted hosts, and base URL for HTTPS.
 */

$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
  return;
}

// Parse PostgreSQL URL: postgres://user:pass@host:port/dbname
$url = parse_url($database_url);
if (!isset($url['scheme'], $url['host'], $url['path'])) {
  return;
}

$databases['default']['default'] = [
  'driver'   => 'pgsql',
  'database' => ltrim($url['path'] ?? '', '/'),
  'username' => $url['user'] ?? '',
  'password' => $url['pass'] ?? '',
  'host'     => $url['host'] ?? 'localhost',
  'port'     => $url['port'] ?? '5432',
  'prefix'   => '',
];

// Trusted host: allow Render URL and optional custom domain
$trusted = ['.onrender.com'];
if ($custom = getenv('TRUSTED_HOST_PATTERNS')) {
  $trusted = array_merge($trusted, array_map('trim', explode(',', $custom)));
}
$settings['trusted_host_patterns'] = array_map(function ($h) {
  return '^' . preg_quote($h, '/') . '$';
}, $trusted);

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
