<?php

/**
 * Enable essential blocks for navigation and functionality
 */

use Drupal\block\Entity\Block;

echo "Enabling essential blocks...\n\n";

// Blocks to enable
$blocks_to_enable = [
  // Site branding (logo/site name)
  [
    'id' => 'storyfulls_theme_branding',
    'plugin' => 'system_branding_block',
    'region' => 'header',
    'weight' => -100,
    'label' => 'Site branding',
    'settings' => [
      'use_site_logo' => TRUE,
      'use_site_name' => TRUE,
      'use_site_slogan' => FALSE,
    ],
  ],
  
  // Main navigation menu
  [
    'id' => 'storyfulls_theme_main_menu',
    'plugin' => 'system_menu_block:main',
    'region' => 'primary_menu',
    'weight' => 0,
    'label' => 'Main navigation',
    'settings' => [
      'level' => 1,
      'depth' => 2,
    ],
  ],
  
  // Footer menu
  [
    'id' => 'storyfulls_theme_footer_menu',
    'plugin' => 'system_menu_block:footer',
    'region' => 'footer',
    'weight' => 0,
    'label' => 'Footer menu',
  ],
];

$created = 0;
$enabled = 0;

foreach ($blocks_to_enable as $block_config) {
  $block_id = $block_config['id'];
  
  try {
    // Check if block exists
    $block = Block::load($block_id);
    
    if (!$block) {
      // Create new block
      $block = Block::create([
        'id' => $block_id,
        'theme' => 'storyfulls_theme',
        'region' => $block_config['region'],
        'weight' => $block_config['weight'],
        'plugin' => $block_config['plugin'],
        'settings' => array_merge([
          'label' => $block_config['label'],
          'label_display' => '0',
        ], $block_config['settings'] ?? []),
      ]);
      $block->save();
      echo "✓ Created: {$block_config['label']}\n";
      $created++;
    } else {
      // Enable existing block
      $block->enable();
      $block->setRegion($block_config['region']);
      $block->setWeight($block_config['weight']);
      $block->save();
      echo "✓ Enabled: {$block_config['label']}\n";
      $enabled++;
    }
  } catch (Exception $e) {
    echo "- Error with {$block_id}: " . $e->getMessage() . "\n";
  }
}

echo "\n✅ Created $created blocks, enabled $enabled blocks\n";
echo "\nNow you have:\n";
echo "- Site logo/branding in header\n";
echo "- Main navigation menu\n";
echo "- Footer menu\n";
