<?php

/**
 * Check purchase link field structure
 */

use Drupal\node\Entity\Node;

$node = Node::load(32); // The Gruffalo

if ($node && $node->hasField('field_purchase_link') && !$node->get('field_purchase_link')->isEmpty()) {
  $link_value = $node->get('field_purchase_link')->first()->getValue();
  echo "\nPurchase Link Structure:\n";
  print_r($link_value);
  
  echo "\n\nURL: " . ($link_value['uri'] ?? 'N/A');
  echo "\nTitle: " . ($link_value['title'] ?? 'N/A');
} else {
  echo "\nNo purchase link found for this book.\n";
}

echo "\n";
