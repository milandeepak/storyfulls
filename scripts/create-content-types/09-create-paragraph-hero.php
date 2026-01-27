<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "🎨 Creating Hero Banner Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'hero_banner',
  'label' => 'Hero Banner',
  'description' => 'Large hero section with background image, heading, and CTA',
]);
$paragraph_type->save();
echo "✓ Hero Banner paragraph type created\n";

// Field 1: Heading
$storage = FieldStorageConfig::loadByName('paragraph', 'field_heading');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_heading',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'hero_banner',
  'label' => 'Heading',
  'required' => TRUE,
])->save();
echo "✓ Heading field added\n";

// Field 2: Subheading
$storage = FieldStorageConfig::loadByName('paragraph', 'field_subheading');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_subheading',
    'entity_type' => 'paragraph',
    'type' => 'string_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'hero_banner',
  'label' => 'Subheading',
  'required' => FALSE,
])->save();
echo "✓ Subheading field added\n";

// Field 3: Background Image
$storage = FieldStorageConfig::loadByName('paragraph', 'field_background_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_background_image',
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'hero_banner',
  'label' => 'Background Image',
  'required' => TRUE,
  'settings' => [
    'file_directory' => 'hero-images',
    'file_extensions' => 'png gif jpg jpeg webp',
    'max_filesize' => '10 MB',
    'alt_field' => TRUE,
    'alt_field_required' => TRUE,
  ],
])->save();
echo "✓ Background Image field added\n";

// Field 4: CTA Button Text
$storage = FieldStorageConfig::loadByName('paragraph', 'field_cta_text');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_cta_text',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'hero_banner',
  'label' => 'Button Text',
  'required' => FALSE,
])->save();
echo "✓ Button Text field added\n";

// Field 5: CTA Button Link
$storage = FieldStorageConfig::loadByName('paragraph', 'field_cta_link');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_cta_link',
    'entity_type' => 'paragraph',
    'type' => 'link',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'hero_banner',
  'label' => 'Button Link',
  'required' => FALSE,
])->save();
echo "✓ Button Link field added\n";

echo "\n✅ HERO BANNER COMPLETE!\n";
