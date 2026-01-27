<?php

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;

echo "=== IMPORTING ALL 16 BOOKS FROM OLD SITE ===\n\n";

$file_system = \Drupal::service('file_system');
$entity_type_manager = \Drupal::entityTypeManager();

// Step 1: Create/Get Age Group Terms
echo "Step 1: Creating Age Group taxonomy terms...\n";
$age_groups = [
  '0-2' => 'Books for children up to 2 years old',
  '2-5' => 'Books for children 2-5 years old',
  '5-8' => 'Books for children 5-8 years old',
  '8-12' => 'Books for children 8-12 years old',
  '13-16' => 'Books for teens 13-16 years old',
];

$age_group_tids = [];
foreach ($age_groups as $name => $description) {
  // Check if term already exists
  $terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'name' => $name,
      'vid' => 'age_group',
    ]);
  
  if (!empty($terms)) {
    $term = reset($terms);
    $age_group_tids[$name] = $term->id();
    echo "  ✓ Age group '$name' already exists (TID: {$term->id()})\n";
  } else {
    $term = Term::create([
      'vid' => 'age_group',
      'name' => $name,
      'description' => ['value' => $description, 'format' => 'plain_text'],
    ]);
    $term->save();
    $age_group_tids[$name] = $term->id();
    echo "  ✓ Created age group '$name' (TID: {$term->id()})\n";
  }
}

// Step 2: Create Author Terms
echo "\nStep 2: Creating Author taxonomy terms...\n";
$authors = [
  'Adam Blade',
  'Roald Dahl',
  'Ruskin Bond',
  'Archer Jeffrey',
  'Elisabetta Dami',
  'R. J. Palacio',
  'Olivia Hope',
  'Tom Percival',
  'Caryl Hart',
  'Laura Purdie Salas',
  'Julia Donaldson',
  'Onjali Q. Rauf',
];

$author_tids = [];
foreach ($authors as $author_name) {
  $terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'name' => $author_name,
      'vid' => 'author',
    ]);
  
  if (!empty($terms)) {
    $term = reset($terms);
    $author_tids[$author_name] = $term->id();
    echo "  ✓ Author '$author_name' already exists (TID: {$term->id()})\n";
  } else {
    $term = Term::create([
      'vid' => 'author',
      'name' => $author_name,
    ]);
    $term->save();
    $author_tids[$author_name] = $term->id();
    echo "  ✓ Created author '$author_name' (TID: {$term->id()})\n";
  }
}

// Step 3: Define all books with metadata
echo "\nStep 3: Importing books...\n";
$books_data = [
  [
    'title' => 'Brutus the Hound of Horror',
    'author' => 'Adam Blade',
    'age_group' => '5-8',
    'cover' => '1.jpg',
  ],
  [
    'title' => 'Charlie and the Chocolate Factory',
    'author' => 'Roald Dahl',
    'age_group' => '13-16',
    'cover' => '2.jpg',
  ],
  [
    'title' => 'Matilda',
    'author' => 'Roald Dahl',
    'age_group' => '2-5',
    'cover' => '3.jpg',
  ],
  [
    'title' => 'Tales from the Childhood',
    'author' => 'Ruskin Bond',
    'age_group' => '2-5',
    'cover' => '4.jpg',
  ],
  [
    'title' => 'The BFG',
    'author' => 'Roald Dahl',
    'age_group' => '8-12',
    'cover' => '5.jpg',
  ],
  [
    'title' => 'The Boy At the Back of the Class',
    'author' => 'Onjali Q. Rauf',
    'age_group' => '5-8',
    'cover' => '6.jpg',
  ],
  [
    'title' => 'The Whistling Schoolboy And Other Stories Of School Life',
    'author' => 'Ruskin Bond',
    'age_group' => '0-2',
    'cover' => '7.jpg',
  ],
  [
    'title' => 'Willy and the Killer Kipper',
    'author' => 'Archer Jeffrey',
    'age_group' => '5-8',
    'cover' => '8.jpg',
  ],
  [
    'title' => 'The Sewer Rat Stink',
    'author' => 'Elisabetta Dami',
    'age_group' => '5-8',
    'cover' => '1_0.jpg',
  ],
  [
    'title' => 'Wonder',
    'author' => 'R. J. Palacio',
    'age_group' => '5-8',
    'cover' => '2_0.jpg',
  ],
  [
    'title' => 'Prankenstein: The Book of Crazy Mischief',
    'author' => 'Ruskin Bond',
    'age_group' => '2-5',
    'cover' => '11.jpg',
  ],
  [
    'title' => 'Be Wild, Little One',
    'author' => 'Olivia Hope',
    'age_group' => '2-5',
    'cover' => 'Be Wild, Little One.jpeg',
  ],
  [
    'title' => "Billy's Bravery",
    'author' => 'Tom Percival',
    'age_group' => '0-2',
    'cover' => "Billy's Bravery.jpeg",
  ],
  [
    'title' => 'Meet the Weather',
    'author' => 'Caryl Hart',
    'age_group' => '2-5',
    'cover' => 'Meet the Weather.jpeg',
  ],
  [
    'title' => 'Zap! Clap! Boom!',
    'author' => 'Laura Purdie Salas',
    'age_group' => '2-5',
    'cover' => 'Zap! Clap! Boom!.jpeg',
  ],
  [
    'title' => 'The Gruffalo',
    'author' => 'Julia Donaldson',
    'age_group' => '2-5',
    'cover' => 'Think Big.jpeg',
  ],
];

$imported_count = 0;
$file_repository = \Drupal::service('file.repository');

foreach ($books_data as $book_data) {
  // Check if book already exists
  $existing = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties([
      'type' => 'book',
      'title' => $book_data['title'],
    ]);
  
  if (!empty($existing)) {
    echo "  ⚠ Book '{$book_data['title']}' already exists, skipping...\n";
    continue;
  }
  
  // Prepare cover image
  $file_id = null;
  $cover_path = 'public://book-covers/' . $book_data['cover'];
  $real_path = $file_system->realpath($cover_path);
  
  if (file_exists($real_path)) {
    // Check if file entity already exists
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $cover_path]);
    
    if (!empty($files)) {
      $file = reset($files);
      $file_id = $file->id();
    } else {
      // Create file entity
      $file_contents = file_get_contents($real_path);
      $file = $file_repository->writeData($file_contents, $cover_path, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
      $file->setPermanent();
      $file->save();
      $file_id = $file->id();
    }
  }
  
  // Create the book node
  $node = Node::create([
    'type' => 'book',
    'title' => $book_data['title'],
    'status' => 1,
    'field_author' => ['target_id' => $author_tids[$book_data['author']]],
    'field_age_group' => ['target_id' => $age_group_tids[$book_data['age_group']]],
  ]);
  
  // Add cover image if available
  if ($file_id) {
    $node->set('field_featured_image', [
      'target_id' => $file_id,
      'alt' => $book_data['title'],
      'title' => $book_data['title'],
    ]);
  }
  
  $node->save();
  $imported_count++;
  
  echo "  ✓ Imported: {$book_data['title']} (NID: {$node->id()})";
  if ($file_id) {
    echo " with cover image";
  }
  echo "\n";
}

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "Summary:\n";
echo "  - Age Groups Created: " . count($age_group_tids) . "\n";
echo "  - Authors Created: " . count($author_tids) . "\n";
echo "  - Books Imported: $imported_count\n";
echo "  - Total Books Processed: " . count($books_data) . "\n\n";

echo "✅ All books are now available in your Drupal 11 site!\n";
echo "View them at: /admin/content (filter by Book)\n";
