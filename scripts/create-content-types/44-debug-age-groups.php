<?php

/**
 * Debug: Check actual age group taxonomy terms and book assignments
 */

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;

echo "\n=== AGE GROUP TAXONOMY TERMS ===\n";

// Load all age group terms
$terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadTree('age_group');

foreach ($terms as $term) {
  echo "\nTerm ID: " . $term->tid;
  echo "\nTerm Name: " . $term->name;
  
  // Count books with this age group
  $query = \Drupal::entityQuery('node')
    ->accessCheck(TRUE)
    ->condition('type', 'book')
    ->condition('status', 1)
    ->condition('field_age_group', $term->tid);
  $count = $query->count()->execute();
  
  echo "\nBooks with this age: " . $count;
  echo "\n" . str_repeat('-', 40) . "\n";
}

echo "\n\n=== BOOKS AND THEIR AGE GROUPS ===\n";

// Load all books
$book_nids = \Drupal::entityQuery('node')
  ->accessCheck(TRUE)
  ->condition('type', 'book')
  ->condition('status', 1)
  ->execute();

$books = Node::loadMultiple($book_nids);

foreach ($books as $book) {
  echo "\nBook ID: " . $book->id();
  echo "\nTitle: " . $book->getTitle();
  
  if ($book->hasField('field_age_group') && !$book->get('field_age_group')->isEmpty()) {
    $age_term = $book->get('field_age_group')->entity;
    if ($age_term) {
      echo "\nAge Group: " . $age_term->getName();
    } else {
      echo "\nAge Group: (term missing)";
    }
  } else {
    echo "\nAge Group: (not set)";
  }
  
  // Check cover image
  if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
    $file = $book->get('field_featured_image')->entity;
    if ($file) {
      echo "\nCover: " . $file->getFileUri();
    } else {
      echo "\nCover: (file entity missing)";
    }
  } else {
    echo "\nCover: (not set)";
  }
  
  echo "\n" . str_repeat('-', 40) . "\n";
}

echo "\n✓ Debug complete!\n\n";
