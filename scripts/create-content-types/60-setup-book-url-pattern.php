<?php

/**
 * Setup URL pattern for Book content type.
 * Books should use /book/{node-id} instead of /node/{node-id}
 */

use Drupal\pathauto\Entity\PathautoPattern;
use Drupal\node\Entity\Node;

echo "Setting up URL pattern for Book content type...\n";

// Check if pattern already exists
$existing_patterns = \Drupal::entityTypeManager()
  ->getStorage('pathauto_pattern')
  ->loadByProperties(['id' => 'book_url_pattern']);

if (!empty($existing_patterns)) {
  echo "Pattern already exists, deleting old pattern...\n";
  foreach ($existing_patterns as $pattern) {
    $pattern->delete();
  }
}

// Create the pathauto pattern
$pattern = PathautoPattern::create([
  'id' => 'book_url_pattern',
  'label' => 'Book URL Pattern',
  'type' => 'canonical_entities:node',
  'pattern' => 'book/[node:nid]',
  'weight' => -5,
]);

// Add selection criteria - only for book content type
$pattern->addSelectionCondition([
  'id' => 'entity_bundle:node',
  'bundles' => [
    'book' => 'book',
  ],
  'negate' => FALSE,
  'context_mapping' => [
    'node' => 'node',
  ],
]);

$pattern->save();

echo "✓ Pathauto pattern created: /book/[node:nid]\n";

// Now update all existing book nodes to use the new pattern
echo "\nUpdating existing book URLs...\n";

$query = \Drupal::entityQuery('node')
  ->accessCheck(TRUE)
  ->condition('type', 'book');

$nids = $query->execute();

if (empty($nids)) {
  echo "No books found to update.\n";
} else {
  echo "Found " . count($nids) . " books to update.\n";
  
  $pathauto_generator = \Drupal::service('pathauto.generator');
  $alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  
  foreach ($nids as $nid) {
    $node = Node::load($nid);
    if ($node) {
      // Delete existing alias if any
      $existing_aliases = $alias_storage->loadByProperties([
        'path' => '/node/' . $nid,
      ]);
      
      foreach ($existing_aliases as $alias) {
        $alias->delete();
      }
      
      // Generate new alias using pathauto
      $pathauto_generator->updateEntityAlias($node, 'update');
      
      $new_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $nid);
      echo "  ✓ Book #{$nid}: {$node->getTitle()} -> {$new_alias}\n";
    }
  }
  
  echo "\n✓ All book URLs updated successfully!\n";
}

echo "\nURL Pattern Setup Complete!\n";
echo "Books will now use URLs like: /book/29 instead of /node/29\n";
