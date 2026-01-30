<?php

/**
 * @file
 * Auto-loader for environment-specific settings.
 *
 * This file should be included at the end of settings.php to automatically
 * load the correct environment configuration.
 *
 * Add this to the end of your settings.php:
 * if (file_exists(__DIR__ . '/settings.autoload.php')) {
 *   include __DIR__ . '/settings.autoload.php';
 * }
 */

// Load Render.com environment configuration when deployed on Render.
if (getenv('RENDER') && file_exists(__DIR__ . '/settings.render.php')) {
  include __DIR__ . '/settings.render.php';
}
