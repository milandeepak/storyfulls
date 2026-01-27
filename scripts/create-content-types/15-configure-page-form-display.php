<?php

echo "⚙️ Configuring Page form display\n\n";

// Load the form display
$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('node.page.default');

if (!$form_display) {
  echo "Creating form display...\n";
  $form_display = \Drupal::entityTypeManager()
    ->getStorage('entity_form_display')
    ->create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ]);
}

// Configure the paragraphs field
$form_display->setComponent('field_content_sections', [
  'type' => 'paragraphs',
  'weight' => 10,
  'settings' => [
    'title' => 'Paragraph',
    'title_plural' => 'Paragraphs',
    'edit_mode' => 'open',
    'add_mode' => 'dropdown',
    'form_display_mode' => 'default',
    'default_paragraph_type' => '',
  ],
  'third_party_settings' => [],
]);

// Hide or configure other fields
$form_display->setComponent('title', [
  'type' => 'string_textfield',
  'weight' => -5,
]);

$form_display->setComponent('body', [
  'type' => 'text_textarea_with_summary',
  'weight' => 0,
]);

$form_display->save();

echo "✓ Form display configured\n";
echo "✓ Paragraphs field added to form\n";
echo "\n✅ Page form is ready! Refresh your browser.\n";
