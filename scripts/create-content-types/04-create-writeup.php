<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🚀 Creating Write-up Content Type\n\n";

// Create vocabulary
try {
  $vocab = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->create([
    'vid' => 'story_type',
    'name' => 'Story Type',
    'description' => 'Types of creative writing',
  ]);
  $vocab->save();
  echo "✓ Story Type vocabulary created\n";
  
  $terms = ['Story', 'Poem', 'Book Review', 'Art'];
  foreach ($terms as $name) {
    \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
      'vid' => 'story_type',
      'name' => $name,
    ])->save();
  }
  echo "✓ Story Type terms created\n";
} catch (\Exception $e) {
  echo "⚠ Vocabulary may already exist\n";
}

// Create content type
$type = \Drupal::entityTypeManager()->getStorage('node_type')->create([
  'type' => 'write_up',
  'name' => 'Write-up',
  'description' => 'Creative writing from young writers',
  'display_submitted' => TRUE,
]);
$type->save();
echo "✓ Write-up content type created\n";

// Featured Image
$storage = FieldStorageConfig::loadByName('node', 'field_featured_image');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'write_up',
    'label' => 'Story Image',
  ])->save();
  echo "✓ Story Image added\n";
}

// Story Type
$storage = FieldStorageConfig::loadByName('node', 'field_story_type');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_story_type',
    'entity_type' => 'node',
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => ['target_type' => 'taxonomy_term'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'write_up',
  'label' => 'Type',
  'required' => TRUE,
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => ['target_bundles' => ['story_type' => 'story_type']],
  ],
])->save();
echo "✓ Story Type field added\n";

// Inspired By
$storage = FieldStorageConfig::loadByName('node', 'field_story_inspired_by');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_story_inspired_by',
    'entity_type' => 'node',
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => ['target_type' => 'node'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'write_up',
  'label' => 'Inspired by (Book)',
  'settings' => [
    'handler' => 'default:node',
    'handler_settings' => ['target_bundles' => ['book' => 'book']],
  ],
])->save();
echo "✓ Inspired By field added\n";

// Age Group
$storage = FieldStorageConfig::loadByName('node', 'field_age_group');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'write_up',
    'label' => 'Age Group',
  ])->save();
  echo "✓ Age Group added\n";
}

echo "\n✅ WRITE-UP COMPLETE!\n";
