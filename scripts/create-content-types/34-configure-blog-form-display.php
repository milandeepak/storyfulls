<?php

use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "🚀 Configuring Blog Form Display...\n";

// Load or create the default form display for blog nodes
$form_display = EntityFormDisplay::load('node.blog.default');
if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'blog',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// 1. Title
$form_display->setComponent('title', [
  'type' => 'string_textfield',
  'weight' => 0,
]);

// 2. Featured Image
$form_display->setComponent('field_featured_image', [
  'type' => 'image_image',
  'weight' => 1,
  'settings' => [
    'progress_indicator' => 'throbber',
    'preview_image_style' => 'thumbnail',
  ],
]);

// 3. Body (Content)
$form_display->setComponent('body', [
  'type' => 'text_textarea_with_summary',
  'weight' => 2,
  'settings' => [
    'rows' => 12,
    'summary_rows' => 3,
    'placeholder' => '',
  ],
]);

// 4. Short Description
$form_display->setComponent('field_short_description', [
  'type' => 'text_textarea',
  'weight' => 3,
  'settings' => [
    'rows' => 3,
    'placeholder' => 'Brief summary for the blog card',
  ],
]);

// 5. Category
$form_display->setComponent('field_blog_category', [
  'type' => 'options_buttons',
  'weight' => 4,
]);

// 6. Tags
$form_display->setComponent('field_tags', [
  'type' => 'entity_reference_autocomplete',
  'weight' => 5,
  'settings' => [
    'match_operator' => 'CONTAINS',
    'match_limit' => 10,
    'size' => 60,
    'placeholder' => '',
  ],
]);

// Hide internal fields
$form_display->removeComponent('created');
$form_display->removeComponent('uid');
$form_display->removeComponent('promote');
$form_display->removeComponent('sticky');

$form_display->save();

echo "✅ Blog Form Display configured successfully!\n";
