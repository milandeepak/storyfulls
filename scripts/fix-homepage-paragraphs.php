<?php

/**
 * Fix broken paragraph references on homepage.
 * Run this script if you get "This entity (paragraph, X) cannot be referenced" errors.
 */

echo "Fixing broken paragraph references on homepage...\n\n";

// Load homepage
$node = \Drupal\node\Entity\Node::load(16);

if (!$node) {
  echo "ERROR: Homepage (node 16) not found!\n";
  exit(1);
}

echo "Loaded: " . $node->getTitle() . " (node 16)\n";

if (!$node->hasField('field_content_sections')) {
  echo "ERROR: field_content_sections not found!\n";
  exit(1);
}

// Get current paragraph references
$current_values = $node->get('field_content_sections')->getValue();
echo "Current paragraph references: " . count($current_values) . "\n\n";

$valid = [];
$broken = [];

// Check each paragraph
foreach ($current_values as $delta => $item) {
  $pid = $item['target_id'];
  
  try {
    $paragraph = \Drupal\paragraphs\Entity\Paragraph::load($pid);
    
    if ($paragraph) {
      $type = $paragraph->getType();
      echo "  ✓ Paragraph {$pid} ({$type}) - OK\n";
      $valid[] = ['target_id' => $pid, 'target_revision_id' => $item['target_revision_id']];
    } else {
      echo "  ✗ Paragraph {$pid} - BROKEN (doesn't exist)\n";
      $broken[] = $pid;
    }
  } catch (\Exception $e) {
    echo "  ✗ Paragraph {$pid} - ERROR: " . $e->getMessage() . "\n";
    $broken[] = $pid;
  }
}

echo "\n";
echo "Valid paragraphs: " . count($valid) . "\n";
echo "Broken paragraphs: " . count($broken) . "\n";

if (count($broken) > 0) {
  echo "\nRemoving broken paragraphs: " . implode(', ', $broken) . "\n";
  
  // Update the node with only valid paragraphs
  $node->set('field_content_sections', $valid);
  $node->save();
  
  echo "\n✓ SUCCESS! Homepage has been fixed.\n";
  echo "You can now edit the page at /node/16/edit\n";
} else {
  echo "\n✓ No broken paragraphs found. Everything is OK!\n";
}
