<?php

/**
 * @file
 * Add librarian-specific fields to User entity.
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "Adding librarian-specific fields to User entity...\n";

$librarian_fields = [
  'field_librarian_name' => [
    'type' => 'string',
    'label' => 'Name',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_librarian_at' => [
    'type' => 'string',
    'label' => 'Librarian At',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_instagram' => [
    'type' => 'string',
    'label' => 'Instagram',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_linkedin' => [
    'type' => 'string',
    'label' => 'LinkedIn',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_social_others' => [
    'type' => 'string',
    'label' => 'Others',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_about_me' => [
    'type' => 'text_long',
    'label' => 'About Me',
    'settings' => [],
    'cardinality' => 1,
    'required' => false,
  ],
  'field_book_recommendations' => [
    'type' => 'string',
    'label' => "Top 5 children's books recommendations",
    'settings' => ['max_length' => 255],
    'cardinality' => 5,
    'required' => false,
  ],
];

foreach ($librarian_fields as $field_name => $config) {
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

echo "\n✅ Librarian fields setup complete!\n";
echo "\nAdded fields:\n";
echo "- Name\n";
echo "- Librarian At\n";
echo "- Instagram, LinkedIn, Others (Social Media)\n";
echo "- About Me\n";
echo "- Top 5 children's books recommendations\n";
