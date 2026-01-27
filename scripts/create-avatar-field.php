<?php

/**
 * @file
 * Create field_avatar for user avatar selection.
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "Creating field_avatar for user avatar selection...\n";

// Create field storage if it doesn't exist
$field_storage = FieldStorageConfig::loadByName('user', 'field_avatar');
if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_avatar',
    'entity_type' => 'user',
    'type' => 'string',
    'cardinality' => 1,
    'settings' => [
      'max_length' => 255,
    ],
  ]);
  $field_storage->save();
  echo "✅ Field storage created for field_avatar\n";
}

// Create field instance if it doesn't exist
$field = FieldConfig::loadByName('user', 'user', 'field_avatar');
if (!$field) {
  $field = FieldConfig::create([
    'field_name' => 'field_avatar',
    'entity_type' => 'user',
    'bundle' => 'user',
    'label' => 'Avatar',
    'description' => 'User avatar selection (elephant, tiger, or rhino)',
    'required' => FALSE,
    'settings' => [],
  ]);
  $field->save();
  echo "✅ Field instance created for field_avatar\n";
}

echo "✅ Avatar field setup complete!\n";
