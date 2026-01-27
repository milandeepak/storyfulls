<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "📚 Creating Books Listing Page\n\n";

// Create a basic page for books listing
$books_page = Node::create([
  'type' => 'page',
  'title' => 'Books',
  'status' => 1,
  'uid' => 1,
]);

$books_page->save();

echo "✓ Created Books page (ID: " . $books_page->id() . ")\n";
echo "✓ Path will be: /node/" . $books_page->id() . "\n";

// Set up a URL alias
$path_alias = \Drupal::entityTypeManager()->getStorage('path_alias')->create([
  'path' => '/node/' . $books_page->id(),
  'alias' => '/books',
  'langcode' => 'en',
]);
$path_alias->save();

echo "✓ Created URL alias: /books\n";

echo "\n✅ Books page created successfully!\n";
echo "Access at: /books\n";
