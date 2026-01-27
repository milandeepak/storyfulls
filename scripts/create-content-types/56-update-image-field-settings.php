<?php

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🖼️  Updating Image Field Settings\n\n";

// Update Card 1 Image
$field_name = 'field_card1_image';
$storage = FieldStorageConfig::loadByName('paragraph', $field_name);
if ($storage) {
  echo "✓ $field_name storage exists\n";
} else {
  echo "Creating $field_name storage...\n";
  $storage = FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
    'settings' => [
      'uri_scheme' => 'public',
      'default_image' => [],
    ],
  ]);
  $storage->save();
  echo "✓ $field_name storage created\n";
}

// Update field config
$field = FieldConfig::loadByName('paragraph', 'interested_section', $field_name);
if ($field) {
  $settings = [
    'file_directory' => 'interested-section/[date:custom:Y]-[date:custom:m]',
    'file_extensions' => 'png gif jpg jpeg webp',
    'max_filesize' => '5 MB',
    'max_resolution' => '2000x2000',
    'min_resolution' => '',
    'alt_field' => TRUE,
    'alt_field_required' => FALSE,
    'title_field' => FALSE,
    'title_field_required' => FALSE,
    'default_image' => [],
    'handler' => 'default:file',
    'handler_settings' => [],
  ];
  $field->set('settings', $settings);
  $field->save();
  echo "✓ $field_name config updated\n";
}

// Update Card 2 Image
$field_name = 'field_card2_image';
$storage = FieldStorageConfig::loadByName('paragraph', $field_name);
if ($storage) {
  echo "✓ $field_name storage exists\n";
} else {
  echo "Creating $field_name storage...\n";
  $storage = FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
    'settings' => [
      'uri_scheme' => 'public',
      'default_image' => [],
    ],
  ]);
  $storage->save();
  echo "✓ $field_name storage created\n";
}

$field = FieldConfig::loadByName('paragraph', 'interested_section', $field_name);
if ($field) {
  $settings = [
    'file_directory' => 'interested-section/[date:custom:Y]-[date:custom:m]',
    'file_extensions' => 'png gif jpg jpeg webp',
    'max_filesize' => '5 MB',
    'max_resolution' => '2000x2000',
    'min_resolution' => '',
    'alt_field' => TRUE,
    'alt_field_required' => FALSE,
    'title_field' => FALSE,
    'title_field_required' => FALSE,
    'default_image' => [],
    'handler' => 'default:file',
    'handler_settings' => [],
  ];
  $field->set('settings', $settings);
  $field->save();
  echo "✓ $field_name config updated\n";
}

// Update Card 3 Image
$field_name = 'field_card3_image';
$storage = FieldStorageConfig::loadByName('paragraph', $field_name);
if ($storage) {
  echo "✓ $field_name storage exists\n";
} else {
  echo "Creating $field_name storage...\n";
  $storage = FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
    'settings' => [
      'uri_scheme' => 'public',
      'default_image' => [],
    ],
  ]);
  $storage->save();
  echo "✓ $field_name storage created\n";
}

$field = FieldConfig::loadByName('paragraph', 'interested_section', $field_name);
if ($field) {
  $settings = [
    'file_directory' => 'interested-section/[date:custom:Y]-[date:custom:m]',
    'file_extensions' => 'png gif jpg jpeg webp',
    'max_filesize' => '5 MB',
    'max_resolution' => '2000x2000',
    'min_resolution' => '',
    'alt_field' => TRUE,
    'alt_field_required' => FALSE,
    'title_field' => FALSE,
    'title_field_required' => FALSE,
    'default_image' => [],
    'handler' => 'default:file',
    'handler_settings' => [],
  ];
  $field->set('settings', $settings);
  $field->save();
  echo "✓ $field_name config updated\n";
}

echo "\n✅ IMAGE FIELDS UPDATED!\n";
