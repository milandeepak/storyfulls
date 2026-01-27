<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "📚 Creating Books Showcase Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'books_showcase',
  'label' => 'Books Showcase',
  'description' => 'Display books filtered by age, genre, or featured',
]);
$paragraph_type->save();
echo "✓ Books Showcase paragraph type created\n";

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
  'bundle' => 'books_showcase',
  'label' => 'Section Title',
  'required' => TRUE,
])->save();
echo "✓ Section Title field added\n";

// Field 2: Filter by Age Group
$storage = FieldStorageConfig::loadByName('paragraph', 'field_filter_age_group');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_filter_age_group',
    'entity_type' => 'paragraph',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'taxonomy_term'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'books_showcase',
  'label' => 'Filter by Age Group',
  'required' => FALSE,
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => ['target_bundles' => ['age_group' => 'age_group']],
  ],
])->save();
echo "✓ Age Group filter added\n";

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
  'bundle' => 'books_showcase',
  'label' => 'Display Style',
  'required' => TRUE,
  'default_value' => [['value' => 'grid']],
])->save();
echo "✓ Display Style field added\n";

// Field 4: Number of Items
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
  'bundle' => 'books_showcase',
  'label' => 'Number of Books to Show',
  'required' => FALSE,
  'default_value' => [['value' => 8]],
])->save();
echo "✓ Number of Items field added\n";

echo "\n✅ BOOKS SHOWCASE COMPLETE!\n";
