<?php

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Add "Number of Books to Show" field to Books by Age Section paragraph.
 */

// Check if paragraph type exists
$paragraph_type = \Drupal::entityTypeManager()
  ->getStorage('paragraphs_type')
  ->load('books_by_age_section');

if (!$paragraph_type) {
  echo "ERROR: Books by Age Section paragraph type not found!\n";
  exit(1);
}

echo "Found Books by Age Section paragraph type\n";

// Field machine name
$field_name = 'field_number_of_books';

// Check if field storage already exists
$field_storage = FieldStorageConfig::loadByName('paragraph', $field_name);

if (!$field_storage) {
  echo "Creating field storage for {$field_name}...\n";
  
  $field_storage = FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'paragraph',
    'type' => 'integer',
    'cardinality' => 1,
    'settings' => [
      'unsigned' => true,
    ],
  ]);
  $field_storage->save();
  echo "Field storage created successfully.\n";
} else {
  echo "Field storage {$field_name} already exists.\n";
}

// Check if field is already attached to this paragraph type
$field_config = FieldConfig::loadByName('paragraph', 'books_by_age_section', $field_name);

if (!$field_config) {
  echo "Attaching field to Books by Age Section paragraph...\n";
  
  $field_config = FieldConfig::create([
    'field_storage' => $field_storage,
    'bundle' => 'books_by_age_section',
    'label' => 'Number of Books to Show (per age group)',
    'description' => 'How many books to display for each age group (default: 20)',
    'required' => FALSE,
    'default_value' => [
      ['value' => 20]
    ],
    'settings' => [
      'min' => 1,
      'max' => 50,
    ],
  ]);
  $field_config->save();
  echo "Field attached successfully.\n";
} else {
  echo "Field {$field_name} already attached to books_by_age_section.\n";
}

// Configure form display
echo "Configuring form display...\n";
$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('paragraph.books_by_age_section.default');

if ($form_display) {
  $form_display->setComponent($field_name, [
    'type' => 'number',
    'weight' => 1,
    'settings' => [
      'placeholder' => '20',
    ],
  ]);
  $form_display->save();
  echo "Form display configured.\n";
} else {
  echo "WARNING: Could not load form display.\n";
}

// Configure view display
echo "Configuring view display...\n";
$view_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('paragraph.books_by_age_section.default');

if ($view_display) {
  // Hide this field in the view (it's only for backend use)
  $view_display->removeComponent($field_name);
  $view_display->save();
  echo "View display configured (field hidden).\n";
} else {
  echo "WARNING: Could not load view display.\n";
}

echo "\n=== SUCCESS ===\n";
echo "Added 'Number of Books to Show' field to Books by Age Section!\n";
echo "Default value: 20 books per age group\n";
echo "Range: 1-50 books\n";
echo "\nYou can now edit the paragraph at /node/16/edit to set the number of books.\n";
