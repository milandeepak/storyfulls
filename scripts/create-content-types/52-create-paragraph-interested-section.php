<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

echo "🎯 Creating 'You Might Be Interested In' Paragraph Type\n\n";

// Create Paragraph Type
$paragraph_type = ParagraphsType::create([
  'id' => 'interested_section',
  'label' => 'You Might Be Interested In',
  'description' => 'Display three cards for Young Readers, Young Writers, and Events',
]);
$paragraph_type->save();
echo "✓ 'You Might Be Interested In' paragraph type created\n";

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
  'bundle' => 'interested_section',
  'label' => 'Section Title',
  'required' => FALSE,
  'default_value' => [['value' => 'You Might Be Interested In']],
])->save();
echo "✓ Section Title field added\n";

// Field 2: Card 1 Title (Young Readers)
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card1_title');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card1_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 1 Title',
  'required' => FALSE,
  'default_value' => [['value' => 'Young Readers']],
])->save();
echo "✓ Card 1 Title field added\n";

// Field 3: Card 1 Description
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card1_description');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card1_description',
    'entity_type' => 'paragraph',
    'type' => 'string_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 1 Description',
  'required' => FALSE,
  'default_value' => [['value' => 'The journey of a storyteller begins with reading']],
])->save();
echo "✓ Card 1 Description field added\n";

// Field 4: Card 1 Button Text
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card1_button_text');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card1_button_text',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 1 Button Text',
  'required' => FALSE,
  'default_value' => [['value' => 'Discover the magic of books']],
])->save();
echo "✓ Card 1 Button Text field added\n";

// Field 5: Card 1 Link
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card1_link');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card1_link',
    'entity_type' => 'paragraph',
    'type' => 'link',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 1 Link',
  'required' => FALSE,
])->save();
echo "✓ Card 1 Link field added\n";

// Field 6: Card 1 Image
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card1_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card1_image',
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 1 Image',
  'required' => FALSE,
])->save();
echo "✓ Card 1 Image field added\n";

// Card 2 - Young Writers
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card2_title');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card2_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 2 Title',
  'required' => FALSE,
  'default_value' => [['value' => 'Young Writers']],
])->save();
echo "✓ Card 2 Title field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card2_description');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card2_description',
    'entity_type' => 'paragraph',
    'type' => 'string_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 2 Description',
  'required' => FALSE,
  'default_value' => [['value' => "A space to celebrate children's literary creations"]],
])->save();
echo "✓ Card 2 Description field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card2_button_text');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card2_button_text',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 2 Button Text',
  'required' => FALSE,
  'default_value' => [['value' => 'Your creativity shines']],
])->save();
echo "✓ Card 2 Button Text field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card2_link');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card2_link',
    'entity_type' => 'paragraph',
    'type' => 'link',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 2 Link',
  'required' => FALSE,
])->save();
echo "✓ Card 2 Link field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card2_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card2_image',
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 2 Image',
  'required' => FALSE,
])->save();
echo "✓ Card 2 Image field added\n";

// Card 3 - Events
$storage = FieldStorageConfig::loadByName('paragraph', 'field_card3_title');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card3_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 3 Title',
  'required' => FALSE,
  'default_value' => [['value' => 'Events']],
])->save();
echo "✓ Card 3 Title field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card3_description');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card3_description',
    'entity_type' => 'paragraph',
    'type' => 'string_long',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 3 Description',
  'required' => FALSE,
  'default_value' => [['value' => "Stay informed about events related to children's literature"]],
])->save();
echo "✓ Card 3 Description field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card3_button_text');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card3_button_text',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 3 Button Text',
  'required' => FALSE,
  'default_value' => [['value' => 'Read more']],
])->save();
echo "✓ Card 3 Button Text field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card3_link');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card3_link',
    'entity_type' => 'paragraph',
    'type' => 'link',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 3 Link',
  'required' => FALSE,
])->save();
echo "✓ Card 3 Link field added\n";

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card3_image');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_card3_image',
    'entity_type' => 'paragraph',
    'type' => 'image',
    'cardinality' => 1,
  ]);
  $storage->save();
}
FieldConfig::create([
  'field_storage' => $storage,
  'bundle' => 'interested_section',
  'label' => 'Card 3 Image',
  'required' => FALSE,
])->save();
echo "✓ Card 3 Image field added\n";

echo "\n✅ 'YOU MIGHT BE INTERESTED IN' COMPLETE!\n";
