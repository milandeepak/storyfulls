<?php

use Drupal\field\Entity\FieldConfig;

echo "=== CONFIGURING BOOK COVER STORAGE ===\n\n";

// Load the book featured image field
$field_config = FieldConfig::load('node.book.field_featured_image');

if ($field_config) {
  // Update settings to use book-covers directory
  $settings = $field_config->getSettings();
  $settings['file_directory'] = 'book-covers';
  $settings['max_filesize'] = '2 MB';
  $settings['file_extensions'] = 'png gif jpg jpeg webp';
  
  $field_config->setSettings($settings);
  $field_config->save();
  
  echo "✅ Updated field configuration:\n";
  echo "   - Upload Directory: book-covers/\n";
  echo "   - Max File Size: 2 MB\n";
  echo "   - Allowed Extensions: png gif jpg jpeg webp\n\n";
} else {
  echo "❌ Field not found\n";
  exit(1);
}

// Create the directory
$file_system = \Drupal::service('file_system');
$directory = 'public://book-covers';

if (!is_dir($file_system->realpath($directory))) {
  if ($file_system->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY)) {
    echo "✅ Created directory: sites/default/files/book-covers/\n\n";
  } else {
    echo "❌ Failed to create directory\n";
    exit(1);
  }
} else {
  echo "✅ Directory already exists: sites/default/files/book-covers/\n\n";
}

echo "=== CONFIGURATION COMPLETE ===\n\n";
echo "From now on, all book cover uploads will be stored in:\n";
echo "📂 web/sites/default/files/book-covers/\n\n";
echo "Next step: Copy old book cover images from the old site.\n";
