<?php

echo "=== BOOKS BY AGE - TESTING DATA ===\n\n";

// Load all books and group by age
$query = \Drupal::entityQuery('node')
  ->accessCheck(TRUE)
  ->condition('type', 'book')
  ->condition('status', 1)
  ->execute();

$books_by_age = [];
$total_books = 0;

if (!empty($query)) {
  $nodes = \Drupal\node\Entity\Node::loadMultiple($query);
  
  foreach ($nodes as $node) {
    $total_books++;
    $age_group = 'No Age Group';
    
    if ($node->hasField('field_age_group') && !$node->get('field_age_group')->isEmpty()) {
      $age_term = $node->get('field_age_group')->entity;
      if ($age_term) {
        $age_group = $age_term->getName();
      }
    }
    
    if (!isset($books_by_age[$age_group])) {
      $books_by_age[$age_group] = [];
    }
    
    $books_by_age[$age_group][] = [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
      'url' => $node->toUrl()->toString(),
    ];
  }
}

echo "Total Books: $total_books\n\n";

foreach ($books_by_age as $age => $books) {
  echo "=== $age (" . count($books) . " books) ===\n";
  foreach ($books as $book) {
    echo "  - [{$book['nid']}] {$book['title']}\n";
    echo "    URL: {$book['url']}\n";
  }
  echo "\n";
}

echo "\n✅ Books are grouped and URLs are ready for linking!\n";
echo "\n📝 TO TEST:\n";
echo "1. Visit your homepage\n";
echo "2. Scroll to 'Books by Age' section\n";
echo "3. Click on any age group avatar (0-2, 3-5, 6-8, 9-12, 13-16)\n";
echo "4. Books for that age should appear below\n";
echo "5. Click on any book cover to go to its detail page\n";
