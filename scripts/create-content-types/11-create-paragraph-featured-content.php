<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "⭐ Creating Featured Content Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'featured_content',
  'label' => 'Featured Content',
  'description' => 'Manually select and feature specific content (books, blogs, events)',
]);
$paragraph_type->save();
echo "✓ Featured Content paragraph type created\n";

// Field 1: Section Title
$storage = FieldStorageConfig::loadByName('paragraph', 'field_section_title');
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'featured_content',
  'label' => 'Section Title',
  'required' => TRUE,
])->save();
echo "✓ Section Title field added\n";

// Field 2: Featured Items
$storage = FieldStorageConfig::loadByName('paragraph', 'field_featured_items');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_featured_items',
    'entity_type' => 'paragraph',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'node'],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'featured_content',
  'label' => 'Featured Items',
  'required' => TRUE,
  'settings' => [
    'handler' => 'default:node',
    'handler_settings' => [
      'target_bundles' => [
        'book' => 'book',
        'blog' => 'blog',
        'event' => 'event',
      ],
    ],
  ],
])->save();
echo "✓ Featured Items field added\n";

// Field 3: Display Style
$storage = FieldStorageConfig::loadByName('paragraph', 'field_display_style');
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'featured_content',
  'label' => 'Display Style',
  'required' => TRUE,
  'default_value' => [['value' => 'grid']],
])->save();
echo "✓ Display Style field added\n";

echo "\n✅ FEATURED CONTENT COMPLETE!\n";
