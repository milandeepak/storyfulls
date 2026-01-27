<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "🚀 Fixing Age Group field on Event...\n";

// 1. Restrict the Field Configuration to 'age_group' vocabulary only
$field_config = FieldConfig::loadByName('node', 'event', 'field_age_group');
if ($field_config) {
  $settings = $field_config->getSetting('handler_settings');
  // Force restriction to age_group vocabulary
  $settings['target_bundles'] = ['age_group' => 'age_group'];
  // Also ensure sort is by name for better UX
  $settings['sort'] = ['field' => 'name', 'direction' => 'ASC'];
  
  $field_config->setSetting('handler_settings', $settings);
  $field_config->save();
  echo "✅ Restricted 'field_age_group' to 'age_group' vocabulary.\n";
} else {
  echo "⚠️ Field 'field_age_group' not found on node.event!\n";
}

// 2. Update Form Display to use Checkboxes
$form_display = EntityFormDisplay::load('node.event.default');
if ($form_display) {
  $form_display->setComponent('field_age_group', [
    'type' => 'options_buttons', // Checkboxes/Radio buttons
    'weight' => 8,
  ]);
  $form_display->save();
  echo "✅ Updated Form Display to use Checkboxes (options_buttons).\n";
} else {
  echo "⚠️ Form display 'node.event.default' not found.\n";
}
