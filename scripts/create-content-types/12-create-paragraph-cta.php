<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "📢 Creating Call to Action Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'call_to_action',
  'label' => 'Call to Action',
  'description' => 'CTA block with heading, text, and button',
]);
$paragraph_type->save();
echo "✓ Call to Action paragraph type created\n";

// Field 1: Heading
$storage = FieldStorageConfig::loadByName('paragraph', 'field_heading');
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'call_to_action',
  'label' => 'Heading',
  'required' => TRUE,
])->save();
echo "✓ Heading field added\n";

// Field 2: Body Text
$storage = FieldStorageConfig::loadByName('paragraph', 'field_body_text');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_body_text',
    'entity_type' => 'paragraph',
    'type' => 'text_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'call_to_action',
  'label' => 'Body Text',
  'required' => FALSE,
])->save();
echo "✓ Body Text field added\n";

// Field 3: Button Text
$storage = FieldStorageConfig::loadByName('paragraph', 'field_cta_text');
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'call_to_action',
  'label' => 'Button Text',
  'required' => TRUE,
])->save();
echo "✓ Button Text field added\n";

// Field 4: Button Link
$storage = FieldStorageConfig::loadByName('paragraph', 'field_cta_link');
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'call_to_action',
  'label' => 'Button Link',
  'required' => TRUE,
])->save();
echo "✓ Button Link field added\n";

// Field 5: Background Color
$storage = FieldStorageConfig::loadByName('paragraph', 'field_background_color');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_background_color',
    'entity_type' => 'paragraph',
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'light' => 'Light',
        'dark' => 'Dark',
        'accent' => 'Accent Color',
      ],
    ],
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'call_to_action',
  'label' => 'Background Color',
  'required' => FALSE,
  'default_value' => [['value' => 'light']],
])->save();
echo "✓ Background Color field added\n";

echo "\n✅ CALL TO ACTION COMPLETE!\n";
