<?php

/**
 * @file
 * Configure user registration form display.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "Configuring user registration form display...\n";

$form_display = EntityFormDisplay::load('user.user.register');

if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'user',
    'bundle' => 'user',
    'mode' => 'register',
    'status' => TRUE,
  ]);
}

// Set field weights and visibility
$components = [
  'field_user_role' => ['weight' => 0, 'type' => 'options_select'],
  'field_first_name' => ['weight' => 1, 'type' => 'string_textfield'],
  'field_last_name' => ['weight' => 2, 'type' => 'string_textfield'],
  'account' => ['weight' => 3],
  'field_date_of_birth' => ['weight' => 4, 'type' => 'datetime_default'],
  'field_gender' => ['weight' => 5, 'type' => 'options_select'],
  'field_city' => ['weight' => 6, 'type' => 'string_textfield'],
  'field_country' => ['weight' => 7, 'type' => 'string_textfield'],
  'field_managed_by_parents' => ['weight' => 8, 'type' => 'boolean_checkbox'],
  'field_public_profile' => ['weight' => 9, 'type' => 'boolean_checkbox'],
  'field_favorite_authors' => ['weight' => 20, 'type' => 'entity_reference_autocomplete'],
  'field_favorite_books' => ['weight' => 21, 'type' => 'entity_reference_autocomplete'],
  'field_favorite_genres' => ['weight' => 22, 'type' => 'options_buttons'],
];

foreach ($components as $field_name => $config) {
  $component_config = [
    'weight' => $config['weight'],
  ];
  
  if (isset($config['type'])) {
    $component_config['type'] = $config['type'];
  }
  
  $form_display->setComponent($field_name, $component_config);
}

// Hide fields that shouldn't be on registration
$hidden_fields = [
  'field_age',
  'field_location',
  'field_bio',
  'field_currently_reading',
  'field_weeks_pick_book',
  'field_weeks_pick_author',
  'field_wishlist',
  'field_books_read',
  'user_picture',
  'timezone',
  'language',
];

foreach ($hidden_fields as $field_name) {
  $form_display->removeComponent($field_name);
}

$form_display->save();

echo "✅ User registration form display configured!\n";
