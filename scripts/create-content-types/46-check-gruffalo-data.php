<?php

/**
 * Check The Gruffalo book data (Node 32)
 */

use Drupal\node\Entity\Node;

$node = Node::load(32);

if (!$node) {
  echo "Book not found!\n";
  exit;
}

echo "\n=== THE GRUFFALO (Node 32) DATA ===\n";
echo "Title: " . $node->getTitle() . "\n\n";

// Check description
echo "--- DESCRIPTION FIELD ---\n";
if ($node->hasField('field_description')) {
  if (!$node->get('field_description')->isEmpty()) {
    $desc = $node->get('field_description')->value;
    echo "Description exists: " . strlen($desc) . " characters\n";
    echo "First 100 chars: " . substr($desc, 0, 100) . "...\n";
  } else {
    echo "Description field is EMPTY\n";
  }
} else {
  echo "Description field does NOT EXIST\n";
}

// Check tags
echo "\n--- TAGS FIELD ---\n";
if ($node->hasField('field_tags')) {
  if (!$node->get('field_tags')->isEmpty()) {
    echo "Tags field has data:\n";
    foreach ($node->get('field_tags')->referencedEntities() as $tag) {
      echo "  - " . $tag->getName() . " (ID: " . $tag->id() . ")\n";
    }
  } else {
    echo "Tags field is EMPTY\n";
  }
} else {
  echo "Tags field does NOT EXIST\n";
}

// List all fields on this node
echo "\n--- ALL FIELDS ON THIS NODE ---\n";
$field_definitions = $node->getFieldDefinitions();
foreach ($field_definitions as $field_name => $field_definition) {
  if (strpos($field_name, 'field_') === 0) {
    $is_empty = $node->get($field_name)->isEmpty() ? 'EMPTY' : 'HAS DATA';
    echo "$field_name: $is_empty\n";
  }
}

echo "\n";
