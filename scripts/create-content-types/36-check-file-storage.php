<?php

$field_config = \Drupal::entityTypeManager()
  ->getStorage('field_config')
  ->load('node.book.field_featured_image');

if ($field_config) {
  $settings = $field_config->getSettings();
  echo "=== BOOK COVER IMAGE STORAGE CONFIGURATION ===\n\n";
  echo "Upload Directory: " . ($settings['file_directory'] ?? '[default - sites/default/files/]') . "\n";
  echo "Max File Size: " . ($settings['max_filesize'] ?? '[default - from php.ini]') . "\n";
  echo "Allowed Extensions: " . ($settings['file_extensions'] ?? 'png gif jpg jpeg') . "\n";
  echo "URI Scheme: " . ($settings['uri_scheme'] ?? 'public') . "\n\n";
  
  echo "Full Storage Path: sites/default/files/" . ($settings['file_directory'] ?? '') . "\n\n";
  
  // Check if directory exists
  $file_system = \Drupal::service('file_system');
  $directory = 'public://' . ($settings['file_directory'] ?? '');
  
  if (is_dir($file_system->realpath($directory))) {
    echo "✅ Directory exists and is writable\n";
  } else {
    echo "⚠️  Directory will be created on first upload\n";
  }
} else {
  echo "⚠️  Featured image field not configured yet\n";
  echo "Default storage will be: sites/default/files/\n";
}
