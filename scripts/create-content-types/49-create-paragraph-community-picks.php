<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "🌟 Creating Community Picks Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'community_picks',
  'label' => 'Community Picks',
  'description' => 'Display books handpicked by a community of parents and bookworms',
]);
$paragraph_type->save();
echo "✓ Community Picks paragraph type created\n";

// Field 1: Section Title
$storage = FieldStorageConfig::loadByName('paragraph', 'field_section_title');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_section_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'community_picks',
  'label' => 'Section Title',
  'required' => FALSE,
  'default_value' => [['value' => 'Discover books handpicked by a community of parents & bookworms.']],
])->save();
echo "✓ Section Title field added\n";

// Field 2: Number of Books to Display
$storage = FieldStorageConfig::loadByName('paragraph', 'field_number_of_items');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_number_of_items',
    'entity_type' => 'paragraph',
    'type' => 'integer',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'community_picks',
  'label' => 'Number of Books to Show',
  'required' => FALSE,
  'default_value' => [['value' => 4]],
])->save();
echo "✓ Number of Items field added\n";

// Field 3: Display Style
$storage = FieldStorageConfig::loadByName('paragraph', 'field_display_style');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_display_style',
    'entity_type' => 'paragraph',
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'grid' => 'Grid',
        'carousel' => 'Carousel',
        'list' => 'List',
      ],
    ],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'community_picks',
  'label' => 'Display Style',
  'required' => FALSE,
  'default_value' => [['value' => 'grid']],
])->save();
echo "✓ Display Style field added\n";

// Field 4: Featured Books (manually selected)
$storage = FieldStorageConfig::loadByName('paragraph', 'field_featured_books');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_featured_books',
    'entity_type' => 'paragraph',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'node'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'community_picks',
  'label' => 'Featured Books',
  'description' => 'Select books to feature in this section. If left empty, will show random books.',
  'required' => FALSE,
  'settings' => [
    'handler' => 'default:node',
    'handler_settings' => ['target_bundles' => ['book' => 'book']],
  ],
])->save();
echo "✓ Featured Books field added\n";

echo "\n✅ COMMUNITY PICKS COMPLETE!\n";
