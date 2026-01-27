<?php

/**
 * Check if drupalSettings are being attached to the paragraph
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

echo "\n=== CHECKING PARAGRAPH ATTACHMENTS ===\n\n";

// Find the homepage node
$nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['title' => 'Homepage Demo']);

if (empty($nodes)) {
  echo "Homepage Demo not found!\n";
  exit;
}

$homepage = reset($nodes);
echo "Found homepage: " . $homepage->id() . "\n";

// Check if it has paragraphs
if (!$homepage->hasField('field_content_sections') || $homepage->get('field_content_sections')->isEmpty()) {
  echo "No paragraphs found on homepage!\n";
  exit;
}

echo "Checking paragraphs...\n\n";

foreach ($homepage->get('field_content_sections')->referencedEntities() as $paragraph) {
  $type = $paragraph->bundle();
  echo "Paragraph type: $type\n";
  
  if ($type === 'books_by_age_section') {
    echo "  ✓ Found Books by Age paragraph!\n";
    echo "  Paragraph ID: " . $paragraph->id() . "\n";
  }
}

echo "\n";
