<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "🔧 Fixing Paragraph Reference Issue\n\n";

// Load the homepage
$homepage_nid = 16;
$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "✓ Found homepage: " . $homepage->getTitle() . "\n";

// Check current paragraphs
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  
  echo "\nCurrent paragraphs:\n";
  $valid_paragraphs = [];
  
  foreach ($current_paragraphs as $index => $paragraph_ref) {
    $paragraph_id = $paragraph_ref['target_id'];
    $paragraph = Paragraph::load($paragraph_id);
    
    if ($paragraph) {
      $bundle = $paragraph->bundle();
      echo "  ✓ Paragraph $paragraph_id ($bundle) - OK\n";
      $valid_paragraphs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    } else {
      echo "  ❌ Paragraph $paragraph_id - NOT FOUND (will be removed)\n";
    }
  }
  
  // Update with only valid paragraphs
  if (count($valid_paragraphs) !== count($current_paragraphs)) {
    echo "\n⚠ Found orphaned paragraph references. Cleaning up...\n";
    $homepage->set('field_content_sections', $valid_paragraphs);
    $homepage->save();
    echo "✅ Homepage updated with valid paragraphs only\n";
  } else {
    echo "\n✓ All paragraph references are valid\n";
  }
}

echo "\n✅ DONE!\n";
