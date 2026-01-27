<?php

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "🎨 Configuring 'You Might Be Interested In' Form & Display\n\n";

// Configure Form Display
$form_display = EntityFormDisplay::load('paragraph.interested_section.default');
if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'interested_section',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

$form_display
  ->setComponent('field_section_title', [
    'type' => 'string_textfield',
    'weight' => 0,
    'settings' => [
      'size' => 60,
      'placeholder' => 'You Might Be Interested In',
    ],
  ])
  // Card 1 fields
  ->setComponent('field_card1_title', [
    'type' => 'string_textfield',
    'weight' => 1,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Young Readers',
    ],
  ])
  ->setComponent('field_card1_description', [
    'type' => 'string_textarea',
    'weight' => 2,
    'settings' => [
      'rows' => 3,
      'placeholder' => 'The journey of a storyteller begins with reading',
    ],
  ])
  ->setComponent('field_card1_button_text', [
    'type' => 'string_textfield',
    'weight' => 3,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Discover the magic of books',
    ],
  ])
  ->setComponent('field_card1_link', [
    'type' => 'link_default',
    'weight' => 4,
  ])
  ->setComponent('field_card1_image', [
    'type' => 'image_image',
    'weight' => 5,
  ])
  // Card 2 fields
  ->setComponent('field_card2_title', [
    'type' => 'string_textfield',
    'weight' => 6,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Young Writers',
    ],
  ])
  ->setComponent('field_card2_description', [
    'type' => 'string_textarea',
    'weight' => 7,
    'settings' => [
      'rows' => 3,
      'placeholder' => "A space to celebrate children's literary creations",
    ],
  ])
  ->setComponent('field_card2_button_text', [
    'type' => 'string_textfield',
    'weight' => 8,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Your creativity shines',
    ],
  ])
  ->setComponent('field_card2_link', [
    'type' => 'link_default',
    'weight' => 9,
  ])
  ->setComponent('field_card2_image', [
    'type' => 'image_image',
    'weight' => 10,
  ])
  // Card 3 fields
  ->setComponent('field_card3_title', [
    'type' => 'string_textfield',
    'weight' => 11,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Events',
    ],
  ])
  ->setComponent('field_card3_description', [
    'type' => 'string_textarea',
    'weight' => 12,
    'settings' => [
      'rows' => 3,
      'placeholder' => "Stay informed about events related to children's literature",
    ],
  ])
  ->setComponent('field_card3_button_text', [
    'type' => 'string_textfield',
    'weight' => 13,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Read more',
    ],
  ])
  ->setComponent('field_card3_link', [
    'type' => 'link_default',
    'weight' => 14,
  ])
  ->setComponent('field_card3_image', [
    'type' => 'image_image',
    'weight' => 15,
  ]);

$form_display->save();
echo "✓ Form display configured\n";

// Configure View Display
$view_display = EntityViewDisplay::load('paragraph.interested_section.default');
if (!$view_display) {
  $view_display = EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'interested_section',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// Hide all fields since we're using custom template
$view_display
  ->removeComponent('field_section_title')
  ->removeComponent('field_card1_title')
  ->removeComponent('field_card1_description')
  ->removeComponent('field_card1_button_text')
  ->removeComponent('field_card1_link')
  ->removeComponent('field_card1_image')
  ->removeComponent('field_card2_title')
  ->removeComponent('field_card2_description')
  ->removeComponent('field_card2_button_text')
  ->removeComponent('field_card2_link')
  ->removeComponent('field_card2_image')
  ->removeComponent('field_card3_title')
  ->removeComponent('field_card3_description')
  ->removeComponent('field_card3_button_text')
  ->removeComponent('field_card3_link')
  ->removeComponent('field_card3_image');

$view_display->save();
echo "✓ View display configured (using custom template)\n";

echo "\n✅ CONFIGURATION COMPLETE!\n";
