<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "🚀 Creating Blog Content Type\n\n";

// Create vocabulary
try {
  $vocab = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->create([
    'vid' => 'blog_category',
    'name' => 'Blog Category',
    'description' => 'Categories for blog posts',
  ]);
  $vocab->save();
  echo "✓ Blog Category vocabulary created\n";
  
  $terms = ['Reading Tips', 'Author Spotlight', 'Book News', 'Literacy Tips', 'Events & Activities'];
  foreach ($terms as $name) {
    \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
      'vid' => 'blog_category',
      'name' => $name,
    ])->save();
  }
  echo "✓ Category terms created\n";
} catch (\Exception $e) {
  echo "⚠ Vocabulary may already exist\n";
}

// Create content type
$type = \Drupal::entityTypeManager()->getStorage('node_type')->create([
  'type' => 'blog',
  'name' => 'Blog',
  'description' => 'Blog posts and articles',
  'display_submitted' => FALSE,
]);
$type->save();
echo "✓ Blog content type created\n";

// Featured Image
$storage = FieldStorageConfig::loadByName('node', 'field_featured_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_featured_image',
    'entity_type' => 'node',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'blog',
  'label' => 'Featured Image',
  'required' => TRUE,
])->save();
echo "✓ Featured Image field added\n";

// Short Description
$storage = FieldStorageConfig::loadByName('node', 'field_short_description');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_short_description',
    'entity_type' => 'node',
    'type' => 'text_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'blog',
  'label' => 'Short Description',
])->save();
echo "✓ Short Description field added\n";

// Category
$storage = FieldStorageConfig::loadByName('node', 'field_blog_category');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_blog_category',
    'entity_type' => 'node',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'taxonomy_term'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'blog',
  'label' => 'Category',
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => ['target_bundles' => ['blog_category' => 'blog_category']],
  ],
])->save();
echo "✓ Category field added\n";

// Tags
$storage = FieldStorageConfig::loadByName('node', 'field_tags');
if ($storage) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'blog',
    'label' => 'Tags',
  ])->save();
  echo "✓ Tags field added\n";
}

echo "\n✅ BLOG COMPLETE!\n";
