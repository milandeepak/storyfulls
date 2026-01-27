<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

// Find the homepage (assuming it's the front page or node ID 16)
$front_page = \Drupal::config('system.site')->get('page.front');
if ($front_page) {
  // Extract node ID from path
  preg_match('/\/node\/(\d+)/', $front_page, $matches);
  $homepage_nid = $matches[1] ?? NULL;
} else {
  // Fallback to node ID 16 if set
  $homepage_nid = 16;
}

if (!$homepage_nid) {
  echo "❌ Could not find homepage. Please specify node ID.\n";
  exit(1);
}

$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "Found homepage: " . $homepage->getTitle() . " (ID: $homepage_nid)\n";

// Create Books by Age Section paragraph
$books_by_age_paragraph = Paragraph::create([
  'type' => 'books_by_age_section',
  'field_section_title' => 'Books By Age',
  'field_section_subtitle' => 'Choose Your Category',
  'field_display_style' => 'grid',
  'field_books_per_section' => 5,
]);
$books_by_age_paragraph->save();

echo "Created Books by Age Section paragraph (ID: " . $books_by_age_paragraph->id() . ")\n";

// Add to homepage's content sections field
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  
  // Add the new paragraph
  $current_paragraphs[] = [
    'target_id' => $books_by_age_paragraph->id(),
    'target_revision_id' => $books_by_age_paragraph->getRevisionId(),
  ];
  
  $homepage->set('field_content_sections', $current_paragraphs);
  $homepage->save();
  
  echo "✅ Added Books by Age Section to homepage!\n";
  echo "View at: " . $homepage->toUrl()->toString() . "\n";
} else {
  echo "❌ Homepage doesn't have field_content_sections field\n";
}
