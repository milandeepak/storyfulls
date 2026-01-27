<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "📄 Adding Paragraphs field to Page content type\n\n";

// Create Paragraphs field storage
$storage = FieldStorageConfig::loadByName('node', 'field_content_sections');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_content_sections',
    'entity_type' => 'node',
    'type' => 'entity_reference_revisions',
    'cardinality' => -1,
    'settings' => ['target_type' => 'paragraph'],
  ]);
  $storage->save();
  echo "✓ Paragraphs field storage created\n";
}

// Add to Page content type
$field = FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'page',
  'label' => 'Content Sections',
  'required' => FALSE,
  'settings' => [
    'handler' => 'default:paragraph',
    'handler_settings' => [
      'target_bundles' => [
        'hero_banner' => 'hero_banner',
        'books_showcase' => 'books_showcase',
        'featured_content' => 'featured_content',
        'call_to_action' => 'call_to_action',
      ],
      'target_bundles_drag_drop' => [
        'hero_banner' => ['enabled' => TRUE],
        'books_showcase' => ['enabled' => TRUE],
        'featured_content' => ['enabled' => TRUE],
        'call_to_action' => ['enabled' => TRUE],
      ],
    ],
  ],
]);
$field->save();
echo "✓ Paragraphs field added to Page\n";

echo "\n✅ Pages can now use Paragraphs!\n";
