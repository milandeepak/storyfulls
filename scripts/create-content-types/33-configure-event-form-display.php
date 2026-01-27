<?php

use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "🚀 Configuring Event Form Display...\n";

// Load or create the default form display for event nodes
$form_display = EntityFormDisplay::load('node.event.default');
if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'event',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// 1. Title (Native)
// Usually handled automatically, but good to ensure weight
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

// 3. Description (Body) - "About this event"
$form_display->setComponent('body', [
  'type' => 'text_textarea_with_summary',
  'weight' => 2,
  'settings' => [
    'rows' => 9,
    'summary_rows' => 3,
    'placeholder' => '',
  ],
]);

// 4. Short Description - "What You Get"
$form_display->setComponent('field_short_description', [
    'type' => 'string_textarea',
    'weight' => 3,
    'settings' => [
        'rows' => 5,
        'placeholder' => "Enter each benefit on a new line.\nExample:\nExpands Vocabulary\nBoosts Reading Enthusiasm",
    ],
]);

// 5. Event Date & Time
$form_display->setComponent('field_event_date', [
  'type' => 'datetime_default',
  'weight' => 4,
]);

// 6. End Date
$form_display->setComponent('field_end_date', [
  'type' => 'datetime_default',
  'weight' => 5,
]);

// 7. Location
$form_display->setComponent('field_event_location', [
  'type' => 'string_textfield',
  'weight' => 6,
]);

// 8. Registration Link
$form_display->setComponent('field_url', [
  'type' => 'link_default',
  'weight' => 7,
  'settings' => [
    'placeholder_url' => 'https://example.com/register',
    'placeholder_title' => 'Register Here',
  ],
]);

// 9. Age Group
$form_display->setComponent('field_age_group', [
  'type' => 'options_select', // Or 'entity_reference_autocomplete'
  'weight' => 8,
]);

// 10. Tags
// Ensure the field is available before setting component to avoid errors if it strictly doesn't exist
// (Though in Drupal setting a component for a non-existent field usually just does nothing or works harmlessly)
$form_display->setComponent('field_tags', [
  'type' => 'entity_reference_autocomplete',
  'weight' => 9,
  'settings' => [
    'match_operator' => 'CONTAINS',
    'match_limit' => 10,
    'size' => 60,
    'placeholder' => '',
  ],
]);

// Hide internal fields if necessary, or just let them fade to bottom
$form_display->removeComponent('created');
$form_display->removeComponent('uid');
$form_display->removeComponent('promote');
$form_display->removeComponent('sticky');

$form_display->save();

echo "✅ Event Form Display configured successfully!\n";
