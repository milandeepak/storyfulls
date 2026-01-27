<?php

/**
 * Create main navigation menu items
 */

use Drupal\menu_link_content\Entity\MenuLinkContent;

echo "Creating main navigation menu...\n\n";

// Menu items to create
$menu_items = [
  [
    'title' => 'Home',
    'link' => 'internal:/',
    'weight' => 0,
  ],
  [
    'title' => 'Books',
    'link' => 'internal:/books',
    'weight' => 1,
  ],
  [
    'title' => 'Blogs',
    'link' => 'internal:/blogs',
    'weight' => 2,
  ],
  [
    'title' => 'Our Readers',
    'link' => 'internal:/our-readers',
    'weight' => 3,
  ],
  [
    'title' => 'Profile',
    'link' => 'internal:/user',
    'weight' => 4,
  ],
];

$created = 0;

foreach ($menu_items as $item) {
  try {
    // Check if menu item already exists
    $query = \Drupal::entityQuery('menu_link_content')
      ->condition('menu_name', 'main')
      ->condition('title', $item['title'])
      ->accessCheck(FALSE);
    $existing = $query->execute();
    
    if (empty($existing)) {
      $menu_link = MenuLinkContent::create([
        'title' => $item['title'],
        'link' => ['uri' => $item['link']],
        'menu_name' => 'main',
        'weight' => $item['weight'],
        'expanded' => FALSE,
      ]);
      $menu_link->save();
      echo "✓ Created: {$item['title']}\n";
      $created++;
    } else {
      echo "- Already exists: {$item['title']}\n";
    }
  } catch (Exception $e) {
    echo "- Error creating {$item['title']}: " . $e->getMessage() . "\n";
  }
}

echo "\n✅ Created $created menu items\n";
echo "Menu items: Home | Books | Blogs | Our Readers | Profile\n";
