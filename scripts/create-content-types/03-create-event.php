<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🚀 Creating Event Content Type\n\n";

// Create content type
$type = \Drupal::entityTypeManager()->getStorage('node_type')->create([
  'type' => 'event',
  'name' => 'Event',
  'description' => 'Events and activities',
  'display_submitted' => FALSE,
]);
$type->save();
echo "✓ Event content type created\n";

// Featured Image
$storage = FieldStorageConfig::loadByName('node', 'field_featured_image');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'event',
    'label' => 'Event Image',
  ])->save();
  echo "✓ Event Image added\n";
}

// Event Date
$storage = FieldStorageConfig::loadByName('node', 'field_event_date');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_event_date',
    'entity_type' => 'node',
    'type' => 'datetime',
    'cardinality' => 1,
    'settings' => ['datetime_type' => 'datetime'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'event',
  'label' => 'Event Date & Time',
  'required' => TRUE,
])->save();
echo "✓ Event Date added\n";

// End Date
$storage = FieldStorageConfig::loadByName('node', 'field_end_date');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_end_date',
    'entity_type' => 'node',
    'type' => 'datetime',
    'cardinality' => 1,
    'settings' => ['datetime_type' => 'datetime'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'event',
  'label' => 'End Date',
])->save();
echo "✓ End Date added\n";

// Location
$storage = FieldStorageConfig::loadByName('node', 'field_event_location');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_event_location',
    'entity_type' => 'node',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'event',
  'label' => 'Location',
])->save();
echo "✓ Location added\n";

// Short Description
$storage = FieldStorageConfig::loadByName('node', 'field_short_description');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'event',
    'label' => 'Short Description',
  ])->save();
  echo "✓ Description added\n";
}

// URL
$storage = FieldStorageConfig::loadByName('node', 'field_url');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_url',
    'entity_type' => 'node',
    'type' => 'link',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'event',
  'label' => 'Registration Link',
])->save();
echo "✓ Registration Link added\n";

// Age Group
$storage = FieldStorageConfig::loadByName('node', 'field_age_group');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'event',
    'label' => 'Age Group',
  ])->save();
  echo "✓ Age Group added\n";
}

echo "\n✅ EVENT COMPLETE!\n";
