<?php

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🚀 Adding Body Field to Blog Content Type...\n\n";

// Check if body field storage exists
$field_storage = FieldStorageConfig::loadByName('node', 'body');

if (!$field_storage) {
  // Create the field storage
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'body',
    'entity_type' => 'node',
    'type' => 'text_with_summary',
    'cardinality' => 1,
  ]);
  $field_storage->save();
  echo "✅ Created body field storage\n";
}

// Check if body field is attached to blog content type
$field = FieldConfig::loadByName('node', 'blog', 'body');

if (!$field) {
  $field = FieldConfig::create([
    'field_storage' => $field_storage,
    'bundle' => 'blog',
    'label' => 'Body',
    'required' => FALSE,
  ]);
  $field->save();
  echo "✅ Attached body field to blog content type\n";
}

// Configure form display
$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('node.blog.default');

if ($form_display) {
  $form_display->setComponent('body', [
    'type' => 'text_textarea_with_summary',
    'weight' => 2,
    'settings' => [
      'rows' => 9,
      'summary_rows' => 3,
      'placeholder' => '',
      'show_summary' => FALSE,
    ],
  ]);
  $form_display->save();
  echo "✅ Configured body field in form display\n";
}

// Configure view display
$view_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('node.blog.default');

if ($view_display) {
  $view_display->setComponent('body', [
    'type' => 'text_default',
    'weight' => 3,
    'label' => 'hidden',
  ]);
  $view_display->save();
  echo "✅ Configured body field in view display\n";
}

// Also add to full display mode
$full_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('node.blog.full');

if (!$full_display) {
  $full_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'node',
      'bundle' => 'blog',
      'mode' => 'full',
      'status' => TRUE,
    ]);
}

$full_display->setComponent('body', [
  'type' => 'text_default',
  'weight' => 3,
  'label' => 'hidden',
]);
$full_display->save();
echo "✅ Configured body field in full display mode\n";

echo "\n✅ Done! Body field added to blog content type\n";
