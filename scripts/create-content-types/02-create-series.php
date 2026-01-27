<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🚀 Creating Series Content Type\n\n";

// Create content type
$type = \Drupal::entityTypeManager()->getStorage('node_type')->create([
  'type' => 'series',
  'name' => 'Series',
  'description' => 'Book series',
  'display_submitted' => FALSE,
]);
$type->save();
echo "✓ Series content type created\n";

// Series Cover Image
$storage = FieldStorageConfig::loadByName('node', 'field_series_cover_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_series_cover_image',
    'entity_type' => 'node',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'series',
  'label' => 'Series Cover Image',
  'required' => TRUE,
])->save();
echo "✓ Cover Image added\n";

// Description
$storage = FieldStorageConfig::loadByName('node', 'field_description');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'series',
    'label' => 'Description',
  ])->save();
  echo "✓ Description added\n";
}

// Books
$storage = FieldStorageConfig::loadByName('node', 'field_books');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_books',
    'entity_type' => 'node',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'node'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'series',
  'label' => 'Books in Series',
  'settings' => [
    'handler' => 'default:node',
    'handler_settings' => ['target_bundles' => ['book' => 'book']],
  ],
])->save();
echo "✓ Books field added\n";

// Author
$storage = FieldStorageConfig::loadByName('node', 'field_author');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'series',
    'label' => 'Author',
  ])->save();
  echo "✓ Author added\n";
}

// Age Group
$storage = FieldStorageConfig::loadByName('node', 'field_age_group');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'series',
    'label' => 'Age Group',
  ])->save();
  echo "✓ Age Group added\n";
}

echo "\n✅ SERIES COMPLETE!\n";
