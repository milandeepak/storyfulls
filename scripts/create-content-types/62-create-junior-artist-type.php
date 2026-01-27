<?php

use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

// 1. Create Content Type
$type = NodeType::load('junior_artist');
if (!$type) {
  $type = NodeType::create([
    'type' => 'junior_artist',
    'name' => 'Junior Artist',
    'description' => 'Content type for users to share their artwork.',
    'display_submitted' => TRUE,
  ]);
  $type->save();
  echo "Created 'Junior Artist' content type.\n";
} else {
  echo "'Junior Artist' content type already exists.\n";
}

// 2. Add Body Field (Description)
$body_storage = FieldStorageConfig::loadByName('node', 'body');
$field = FieldConfig::loadByName('node', 'junior_artist', 'body');
if (empty($field)) {
  $field = FieldConfig::create([
    'field_storage' => $body_storage,
    'bundle' => 'junior_artist',
    'label' => 'Description',
    'settings' => ['display_summary' => TRUE],
  ]);
  $field->save();
  echo "Added body field (Description).\n";
}

// 3. Add Feature Image Field
// Reuse existing field_featured_image if possible
$image_storage = FieldStorageConfig::loadByName('node', 'field_featured_image');
if (!$image_storage) {
    // Should exist from other content types, but just in case
    $image_storage = FieldStorageConfig::create([
      'field_name' => 'field_featured_image',
      'entity_type' => 'node',
      'type' => 'image',
    ]);
    $image_storage->save();
}

$image_field = FieldConfig::loadByName('node', 'junior_artist', 'field_featured_image');
if (empty($image_field)) {
  $image_field = FieldConfig::create([
    'field_storage' => $image_storage,
    'bundle' => 'junior_artist',
    'label' => 'Artwork',
    'required' => TRUE,
    'settings' => [
      'file_extensions' => 'png gif jpg jpeg',
      'alt_field' => TRUE,
    ],
  ]);
  $image_field->save();
  echo "Added featured image field (Artwork).\n";
}

// 4. Configure Form Display
$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('node.junior_artist.default');

if (!$form_display) {
  $form_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'node',
      'bundle' => 'junior_artist',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$form_display->setComponent('title', [
  'type' => 'string_textfield',
  'weight' => 0,
])
->setComponent('field_featured_image', [
  'type' => 'image_image',
  'weight' => 1,
])
->setComponent('body', [
  'type' => 'text_textarea_with_summary',
  'weight' => 2,
])
->save();
echo "Configured form display.\n";

// 5. Configure View Display
$view_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('node.junior_artist.default');

if (!$view_display) {
  $view_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'node',
      'bundle' => 'junior_artist',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$view_display->setComponent('field_featured_image', [
  'label' => 'hidden',
  'type' => 'image',
  'weight' => 0,
])
->setComponent('body', [
  'label' => 'hidden',
  'type' => 'text_default',
  'weight' => 1,
])
->save();
echo "Configured view display.\n";
