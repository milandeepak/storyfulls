<?php

echo "👁️ Configuring Paragraph View Displays\n\n";

// Configure Hero Banner view display
$hero_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('paragraph.hero_banner.default');

if (!$hero_display) {
  $hero_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'hero_banner',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$hero_display->setComponent('field_heading', [
  'type' => 'string',
  'weight' => 0,
  'label' => 'hidden',
]);
$hero_display->setComponent('field_subheading', [
  'type' => 'string',
  'weight' => 1,
  'label' => 'hidden',
]);
$hero_display->setComponent('field_background_image', [
  'type' => 'image',
  'weight' => -1,
  'label' => 'hidden',
  'settings' => [
    'image_style' => 'large',
    'image_link' => '',
  ],
]);
$hero_display->setComponent('field_cta_text', [
  'type' => 'string',
  'weight' => 2,
  'label' => 'hidden',
]);
$hero_display->setComponent('field_cta_link', [
  'type' => 'link',
  'weight' => 3,
  'label' => 'hidden',
]);
$hero_display->save();
echo "✓ Hero Banner display configured\n";

// Configure Books Showcase view display
$books_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('paragraph.books_showcase.default');

if (!$books_display) {
  $books_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'books_showcase',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$books_display->setComponent('field_section_title', [
  'type' => 'string',
  'weight' => 0,
  'label' => 'hidden',
]);
$books_display->setComponent('field_filter_age_group', [
  'type' => 'entity_reference_label',
  'weight' => 1,
  'label' => 'above',
]);
$books_display->setComponent('field_display_style', [
  'type' => 'list_default',
  'weight' => 2,
  'label' => 'above',
]);
$books_display->setComponent('field_number_of_items', [
  'type' => 'number_integer',
  'weight' => 3,
  'label' => 'above',
]);
$books_display->save();
echo "✓ Books Showcase display configured\n";

// Configure Featured Content view display
$featured_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('paragraph.featured_content.default');

if (!$featured_display) {
  $featured_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'featured_content',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$featured_display->setComponent('field_section_title', [
  'type' => 'string',
  'weight' => 0,
  'label' => 'hidden',
]);
$featured_display->setComponent('field_featured_items', [
  'type' => 'entity_reference_label',
  'weight' => 1,
  'label' => 'hidden',
  'settings' => [
    'link' => TRUE,
  ],
]);
$featured_display->setComponent('field_display_style', [
  'type' => 'list_default',
  'weight' => 2,
  'label' => 'above',
]);
$featured_display->save();
echo "✓ Featured Content display configured\n";

// Configure Call to Action view display
$cta_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('paragraph.call_to_action.default');

if (!$cta_display) {
  $cta_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'call_to_action',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$cta_display->setComponent('field_heading', [
  'type' => 'string',
  'weight' => 0,
  'label' => 'hidden',
]);
$cta_display->setComponent('field_body_text', [
  'type' => 'text_default',
  'weight' => 1,
  'label' => 'hidden',
]);
$cta_display->setComponent('field_cta_text', [
  'type' => 'string',
  'weight' => 2,
  'label' => 'hidden',
]);
$cta_display->setComponent('field_cta_link', [
  'type' => 'link',
  'weight' => 3,
  'label' => 'hidden',
]);
$cta_display->setComponent('field_background_color', [
  'type' => 'list_default',
  'weight' => -1,
  'label' => 'hidden',
]);
$cta_display->save();
echo "✓ Call to Action display configured\n";

// Configure the field_content_sections on page to show paragraphs
$page_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('node.page.default');

if ($page_display) {
  $page_display->setComponent('field_content_sections', [
    'type' => 'entity_reference_revisions_entity_view',
    'weight' => 10,
    'label' => 'hidden',
    'settings' => [
      'view_mode' => 'default',
      'link' => FALSE,
    ],
  ]);
  $page_display->save();
  echo "✓ Page display configured to show paragraphs\n";
}

echo "\n✅ All paragraph displays configured!\n";
