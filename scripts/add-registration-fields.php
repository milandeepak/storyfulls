<?php

/**
 * @file
 * Add additional user registration fields.
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "Adding registration fields to User entity...\n";

$user_fields = [
  'field_first_name' => [
    'type' => 'string',
    'label' => 'First Name',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => true,
  ],
  'field_last_name' => [
    'type' => 'string',
    'label' => 'Last Name',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => true,
  ],
  'field_date_of_birth' => [
    'type' => 'datetime',
    'label' => 'Date of Birth',
    'settings' => ['datetime_type' => 'date'],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_gender' => [
    'type' => 'list_string',
    'label' => 'Gender',
    'settings' => [
      'allowed_values' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer_not_to_say' => 'Prefer not to say',
      ],
    ],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_city' => [
    'type' => 'string',
    'label' => 'City',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_country' => [
    'type' => 'string',
    'label' => 'Country',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_user_role' => [
    'type' => 'list_string',
    'label' => 'User Role',
    'settings' => [
      'allowed_values' => [
        'young_reader' => 'Young Reader',
        'librarian' => 'Librarian',
      ],
    ],
    'cardinality' => 1,
    'required' => true,
  ],
  'field_public_profile' => [
    'type' => 'boolean',
    'label' => 'Show profile to public',
    'settings' => [],
    'cardinality' => 1,
    'required' => false,
  ],
];

foreach ($user_fields as $field_name => $config) {
  // Create storage
  if (!FieldStorageConfig::loadByName('user', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'user',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'],
    ])->save();
    echo "Created storage for {$field_name}\n";
  } else {
    echo "Storage for {$field_name} already exists\n";
  }
  
  // Create field instance
  if (!FieldConfig::loadByName('user', 'user', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => $config['label'],
      'required' => $config['required'] ?? false,
    ])->save();
    echo "Created field instance for {$field_name}\n";
  } else {
    echo "Field instance for {$field_name} already exists\n";
  }
}

echo "\n✅ Registration fields setup complete!\n";
echo "\nAdded fields:\n";
echo "- First Name, Last Name\n";
echo "- Date of Birth, Gender\n";
echo "- City, Country\n";
echo "- User Role (Young Reader/Librarian)\n";
echo "- Public Profile (checkbox)\n";
