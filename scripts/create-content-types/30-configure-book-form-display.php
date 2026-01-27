<?php

/**
 * Configure Book content type form display
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "Configuring Book form display...\n\n";

// Load or create the form display for Book content type
$form_display = EntityFormDisplay::load('node.book.default');

if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'book',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// Configure field components with their weights and settings
$components = [
  'title' => [
    'type' => 'string_textfield',
    'weight' => 0,
    'region' => 'content',
    'settings' => [
      'size' => 60,
      'placeholder' => 'Enter book title',
    ],
  ],
  'field_featured_image' => [
    'type' => 'image_image',
    'weight' => 1,
    'region' => 'content',
    'settings' => [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'thumbnail',
    ],
  ],
  'field_author' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 2,
    'region' => 'content',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Start typing author name...',
    ],
  ],
  'field_description' => [
    'type' => 'text_textarea',
    'weight' => 3,
    'region' => 'content',
    'settings' => [
      'rows' => 5,
      'placeholder' => 'Enter book description...',
    ],
  ],
  'field_age_group' => [
    'type' => 'options_buttons',
    'weight' => 4,
    'region' => 'content',
  ],
  'field_genere' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 5,
    'region' => 'content',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Select genres...',
    ],
  ],
  'field_publisher' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 6,
    'region' => 'content',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Enter publisher name...',
    ],
  ],
  'field_illustrator' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 7,
    'region' => 'content',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Enter illustrator name...',
    ],
  ],
  'field_launch_date' => [
    'type' => 'datetime_default',
    'weight' => 8,
    'region' => 'content',
  ],
  'field_series' => [
    'type' => 'string_textfield',
    'weight' => 9,
    'region' => 'content',
    'settings' => [
      'size' => 60,
      'placeholder' => 'Enter series name if applicable...',
    ],
  ],
  'field_purchase_link' => [
    'type' => 'link_default',
    'weight' => 10,
    'region' => 'content',
    'settings' => [
      'placeholder_url' => 'https://example.com',
      'placeholder_title' => 'Buy this book',
    ],
  ],
  'field_is_inclusive_book' => [
    'type' => 'boolean_checkbox',
    'weight' => 11,
    'region' => 'content',
    'settings' => [
      'display_label' => TRUE,
    ],
  ],
  'field_upcoming_release' => [
    'type' => 'boolean_checkbox',
    'weight' => 12,
    'region' => 'content',
    'settings' => [
      'display_label' => TRUE,
    ],
  ],
  'field_tags' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 13,
    'region' => 'content',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Add tags...',
    ],
  ],
];

// Set all components
foreach ($components as $field_name => $component) {
  $form_display->setComponent($field_name, $component);
  echo "✓ Configured: $field_name\n";
}

// Save the form display
$form_display->save();

echo "\n✅ Book form display configured successfully!\n";
echo "Refresh /node/add/book to see all fields.\n";
