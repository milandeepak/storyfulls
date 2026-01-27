<?php

use Drupal\field\Entity\FieldConfig;

// Fix Favorite Genres (Restrict to 'genre' vocabulary)
$field = FieldConfig::loadByName('user', 'user', 'field_favorite_genres');
if ($field) {
  $settings = $field->getSettings();
  // Ensure we are using the 'default:taxonomy_term' handler or similar if needed, 
  // but just setting target_bundles usually works if handler is default.
  // We assume handler is 'default:taxonomy_term' for tax fields.
  $settings['handler_settings']['target_bundles'] = ['genre' => 'genre'];
  // Auto-create? No.
  $settings['handler_settings']['auto_create'] = FALSE;
  $field->setSettings($settings);
  $field->save();
  echo "Fixed field_favorite_genres\n";
} else {
  echo "field_favorite_genres not found\n";
}

// Fix Favorite Authors (Restrict to 'author' vocabulary)
$field = FieldConfig::loadByName('user', 'user', 'field_favorite_authors');
if ($field) {
  $settings = $field->getSettings();
  $settings['handler_settings']['target_bundles'] = ['author' => 'author'];
  $settings['handler_settings']['auto_create'] = FALSE; // User selects existing
  $field->setSettings($settings);
  $field->save();
  echo "Fixed field_favorite_authors\n";
} else {
  echo "field_favorite_authors not found\n";
}

// Fix Favorite Books (Restrict to 'book' content type)
$field = FieldConfig::loadByName('user', 'user', 'field_favorite_books');
if ($field) {
  $settings = $field->getSettings();
  // Handler for nodes is usually 'default:node'
  $settings['handler_settings']['target_bundles'] = ['book' => 'book'];
  $settings['handler_settings']['auto_create'] = FALSE;
  $field->setSettings($settings);
  $field->save();
  echo "Fixed field_favorite_books\n";
} else {
  echo "field_favorite_books not found\n";
}
