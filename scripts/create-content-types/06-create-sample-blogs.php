<?php

echo "📝 Creating Sample Blog Posts\n\n";

$blogs = [
  [
    'title' => '10 Tips to Get Your Kids Excited About Reading',
    'short_description' => 'Simple strategies to help children fall in love with books.',
    'body' => 'Reading is a magical journey that opens up worlds of imagination. Here are our top 10 tips to make reading fun for kids of all ages...',
    'category' => 'Reading Tips',
  ],
  [
    'title' => 'Author Spotlight: Sarah Johnson',
    'short_description' => 'Meet the author behind The Magical Forest Adventure series.',
    'body' => 'This month we sit down with beloved children\'s author Sarah Johnson to discuss her inspiration, writing process, and what\'s next...',
    'category' => 'Author Spotlight',
  ],
  [
    'title' => 'New Releases: Spring 2024 Must-Reads',
    'short_description' => 'The best new children\'s books hitting shelves this spring.',
    'body' => 'Spring brings a fresh crop of amazing children\'s books! From picture books to middle grade adventures, here are our top picks...',
    'category' => 'Book News',
  ],
];

foreach ($blogs as $blog_data) {
  // Get or create category
  $cat_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
    'vid' => 'blog_category',
    'name' => $blog_data['category'],
  ]);
  $cat_id = $cat_terms ? reset($cat_terms)->id() : null;
  
  $node = \Drupal::entityTypeManager()->getStorage('node')->create([
    'type' => 'blog',
    'title' => $blog_data['title'],
    'field_short_description' => [
      'value' => $blog_data['short_description'],
      'format' => 'basic_html',
    ],
    'body' => [
      'value' => $blog_data['body'],
      'format' => 'basic_html',
    ],
    'field_blog_category' => $cat_id ? [['target_id' => $cat_id]] : [],
    'status' => 1,
  ]);
  $node->save();
  
  echo "✓ Created: {$blog_data['title']}\n";
}

echo "\n✅ " . count($blogs) . " blog posts created!\n";
