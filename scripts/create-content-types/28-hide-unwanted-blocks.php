<?php

/**
 * Hide unwanted blocks from the theme
 */

use Drupal\block\Entity\Block;

echo "Hiding unwanted blocks...\n\n";

// List of block IDs to disable
$blocks_to_disable = [
  'storyfulls_theme_page_title',
  'storyfulls_theme_powered',
  'storyfulls_theme_branding',
  'storyfulls_theme_breadcrumbs',
  'storyfulls_theme_search',
  'storyfulls_theme_local_tasks',
  'storyfulls_theme_local_actions',
  'storyfulls_theme_messages',
  'storyfulls_theme_help',
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
    } else {
      echo "- Not found: $block_id\n";
    }
  } catch (Exception $e) {
    echo "- Error with $block_id: " . $e->getMessage() . "\n";
  }
}

echo "\n✅ Disabled $disabled_count blocks\n";

// Also, let's list all active blocks for the theme
echo "\n=== Active blocks in storyfulls_theme ===\n";
$block_storage = \Drupal::entityTypeManager()->getStorage('block');
$blocks = $block_storage->loadByProperties(['theme' => 'storyfulls_theme']);

foreach ($blocks as $block) {
  if ($block->status()) {
    echo "- " . $block->id() . " (" . $block->label() . ") in region: " . $block->getRegion() . "\n";
  }
}
