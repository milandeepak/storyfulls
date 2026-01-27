<?php

/**
 * Hide admin/search blocks
 */

use Drupal\block\Entity\Block;

echo "Hiding admin and search blocks...\n\n";

$blocks_to_disable = [
  'storyfulls_theme_primary_admin_actions',
  'storyfulls_theme_primary_local_tasks',
  'storyfulls_theme_secondary_local_tasks',
  'storyfulls_theme_search_form_narrow',
  'storyfulls_theme_search_form_wide',
  'storyfulls_theme_site_branding',
];

$disabled_count = 0;

foreach ($blocks_to_disable as $block_id) {
  try {
    $block = Block::load($block_id);
    if ($block) {
      $block->disable();
      $block->save();
      echo "✓ Disabled: $block_id (" . $block->label() . ")\n";
      $disabled_count++;
    }
  } catch (Exception $e) {
    echo "- Error: " . $e->getMessage() . "\n";
  }
}

echo "\n✅ Disabled $disabled_count blocks\n";
echo "Only Main page content and navigation remain active.\n";
