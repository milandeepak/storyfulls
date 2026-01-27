<?php

echo "⚙️ Configuring Paragraph Form Displays\n\n";

// Configure Hero Banner form display
$hero_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('paragraph.hero_banner.default');

if (!$hero_display) {
  $hero_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'hero_banner',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$hero_display->setComponent('field_heading', [
  'type' => 'string_textfield',
  'weight' => 0,
]);
$hero_display->setComponent('field_subheading', [
  'type' => 'string_textarea',
  'weight' => 1,
]);
$hero_display->setComponent('field_background_image', [
  'type' => 'image_image',
  'weight' => 2,
]);
$hero_display->setComponent('field_cta_text', [
  'type' => 'string_textfield',
  'weight' => 3,
]);
$hero_display->setComponent('field_cta_link', [
  'type' => 'link_default',
  'weight' => 4,
]);
$hero_display->save();
echo "✓ Hero Banner form configured\n";

// Configure Books Showcase form display
$books_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('paragraph.books_showcase.default');

if (!$books_display) {
  $books_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'books_showcase',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$books_display->setComponent('field_section_title', [
  'type' => 'string_textfield',
  'weight' => 0,
]);
$books_display->setComponent('field_filter_age_group', [
  'type' => 'options_select',
  'weight' => 1,
]);
$books_display->setComponent('field_display_style', [
  'type' => 'options_select',
  'weight' => 2,
]);
$books_display->setComponent('field_number_of_items', [
  'type' => 'number',
  'weight' => 3,
]);
$books_display->save();
echo "✓ Books Showcase form configured\n";

// Configure Featured Content form display
$featured_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('paragraph.featured_content.default');

if (!$featured_display) {
  $featured_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'featured_content',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$featured_display->setComponent('field_section_title', [
  'type' => 'string_textfield',
  'weight' => 0,
]);
$featured_display->setComponent('field_featured_items', [
  'type' => 'entity_reference_autocomplete',
  'weight' => 1,
]);
$featured_display->setComponent('field_display_style', [
  'type' => 'options_select',
  'weight' => 2,
]);
$featured_display->save();
echo "✓ Featured Content form configured\n";

// Configure Call to Action form display
$cta_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('paragraph.call_to_action.default');

if (!$cta_display) {
  $cta_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'paragraph',
      'bundle' => 'call_to_action',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

$cta_display->setComponent('field_heading', [
  'type' => 'string_textfield',
  'weight' => 0,
]);
$cta_display->setComponent('field_body_text', [
  'type' => 'text_textarea',
  'weight' => 1,
]);
$cta_display->setComponent('field_cta_text', [
  'type' => 'string_textfield',
  'weight' => 2,
]);
$cta_display->setComponent('field_cta_link', [
  'type' => 'link_default',
  'weight' => 3,
]);
$cta_display->setComponent('field_background_color', [
  'type' => 'options_select',
  'weight' => 4,
]);
$cta_display->save();
echo "✓ Call to Action form configured\n";

echo "\n✅ All paragraph form displays configured!\n";
