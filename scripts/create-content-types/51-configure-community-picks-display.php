<?php

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "🎨 Configuring Community Picks Form & Display\n\n";

// Configure Form Display
$form_display = EntityFormDisplay::load('paragraph.community_picks.default');
if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'community_picks',
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
      'placeholder' => 'Discover books handpicked by a community of parents & bookworms.',
    ],
  ])
  ->setComponent('field_number_of_items', [
    'type' => 'number',
    'weight' => 1,
    'settings' => [
      'placeholder' => '4',
    ],
  ])
  ->setComponent('field_display_style', [
    'type' => 'options_select',
    'weight' => 2,
  ])
  ->setComponent('field_featured_books', [
    'type' => 'entity_reference_autocomplete',
    'weight' => 3,
    'settings' => [
      'match_operator' => 'CONTAINS',
      'size' => 60,
      'placeholder' => 'Start typing book title...',
    ],
  ]);

$form_display->save();
echo "✓ Form display configured\n";

// Configure View Display
$view_display = EntityViewDisplay::load('paragraph.community_picks.default');
if (!$view_display) {
  $view_display = EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'community_picks',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// Hide all fields since we're using custom template
$view_display
  ->removeComponent('field_section_title')
  ->removeComponent('field_number_of_items')
  ->removeComponent('field_display_style')
  ->removeComponent('field_featured_books');

$view_display->save();
echo "✓ View display configured (using custom template)\n";

echo "\n✅ CONFIGURATION COMPLETE!\n";
