<?php

/**
 * Test what books data is being passed to JavaScript
 */

use Drupal\node\Entity\Node;

echo "\n=== TESTING BOOKS BY AGE DATA ===\n\n";

// Simulate what the preprocess function does
$all_books_query = \Drupal::entityQuery('node')
  ->accessCheck(TRUE)
  ->condition('type', 'book')
  ->condition('status', 1)
  ->execute();

$all_books_by_age = [];

if (!empty($all_books_query)) {
  $all_nodes = Node::loadMultiple($all_books_query);
  foreach ($all_nodes as $node) {
    $age_group = NULL;
    if ($node->hasField('field_age_group') && !$node->get('field_age_group')->isEmpty()) {
      $age_term = $node->get('field_age_group')->entity;
      if ($age_term) {
        $age_group = $age_term->getName();
      }
    }
    
    if ($age_group) {
      // Normalize age group names
      $normalized_age = $age_group;
      $age_mapping = [
        '3-5' => '2-5',
        '6-8' => '5-8',
        '9-12' => '8-12',
      ];
      
      if (isset($age_mapping[$age_group])) {
        $normalized_age = $age_mapping[$age_group];
      }
      
      $ages_to_populate = [$normalized_age];
      if ($age_group !== $normalized_age) {
        $ages_to_populate[] = $age_group;
      }
      
      $book_data = [
        'nid' => $node->id(),
        'title' => $node->getTitle(),
        'url' => $node->toUrl()->toString(),
      ];
      
      if ($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()) {
        $file = $node->get('field_featured_image')->entity;
        if ($file) {
          $book_data['cover_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
      
      foreach ($ages_to_populate as $age_key) {
        if (!isset($all_books_by_age[$age_key])) {
          $all_books_by_age[$age_key] = [];
        }
        $all_books_by_age[$age_key][] = $book_data;
      }
    }
  }
}

echo "Books grouped by age:\n\n";
foreach ($all_books_by_age as $age => $books) {
  echo "AGE: $age\n";
  echo "  Count: " . count($books) . " books\n";
  foreach ($books as $book) {
    echo "  - " . $book['title'] . " (ID: " . $book['nid'] . ")\n";
  }
  echo "\n";
}

echo "\nThis data should be available in JavaScript as drupalSettings.booksByAge\n\n";
