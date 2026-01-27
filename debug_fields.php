<?php

use Drupal\field\Entity\FieldConfig;

$fields = [
  'field_favorite_genres',
  'field_favorite_authors',
  'field_favorite_books'
];

foreach ($fields as $field_name) {
  $field = FieldConfig::loadByName('user', 'user', $field_name);
  if ($field) {
    echo "Field: $field_name\n";
    $settings = $field->getSettings();
    print_r($settings['handler_settings'] ?? 'No handler settings');
    echo "\n-------------------\n";
  } else {
    echo "Field $field_name not found.\n";
  }
}
