<?php

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

// Create Books by Age Section paragraph type
$paragraph_type = ParagraphsType::create([
  'id' => 'books_by_age_section',
  'label' => 'Books by Age Section',
  'description' => 'Displays books organized by age groups with circular avatars',
]);
$paragraph_type->save();

echo "Created paragraph type: Books by Age Section\n";

// Add Section Title field
$field_storage = FieldStorageConfig::loadByName('paragraph', 'field_section_title');
if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_section_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $field_storage->save();
}

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'books_by_age_section',
  'label' => 'Section Title',
  'required' => FALSE,
  'default_value' => [['value' => 'Books By Age']],
]);
$field->save();

echo "Added field: Section Title\n";

// Add Section Subtitle field
$field_storage = FieldStorageConfig::loadByName('paragraph', 'field_section_subtitle');
if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_section_subtitle',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $field_storage->save();
}

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'books_by_age_section',
  'label' => 'Section Subtitle',
  'required' => FALSE,
  'default_value' => [['value' => 'Choose Your Category']],
]);
$field->save();

echo "Added field: Section Subtitle\n";

// Add Display Style field (list)
$field_storage = FieldStorageConfig::loadByName('paragraph', 'field_display_style');
if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_display_style',
    'entity_type' => 'paragraph',
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'grid' => 'Grid',
        'carousel' => 'Carousel',
      ],
    ],
  ]);
  $field_storage->save();
}

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'books_by_age_section',
  'label' => 'Display Style',
  'required' => FALSE,
  'default_value' => [['value' => 'grid']],
]);
$field->save();

echo "Added field: Display Style\n";

// Add Books Per Section field
$field_storage = FieldStorageConfig::loadByName('paragraph', 'field_books_per_section');
if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_books_per_section',
    'entity_type' => 'paragraph',
    'type' => 'integer',
    'cardinality' => 1,
  ]);
  $field_storage->save();
}

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'books_by_age_section',
  'label' => 'Books to Display',
  'description' => 'Number of books to show (default: 5)',
  'required' => FALSE,
  'default_value' => [['value' => 5]],
]);
$field->save();

echo "Added field: Books to Display\n";

echo "\n✅ Books by Age Section paragraph type created successfully!\n";
