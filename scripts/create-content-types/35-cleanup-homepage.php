<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

// Find the homepage
$front_page = \Drupal::config('system.site')->get('page.front');
if ($front_page) {
  preg_match('/\/node\/(\d+)/', $front_page, $matches);
  $homepage_nid = $matches[1] ?? NULL;
} else {
  $homepage_nid = 16; // Fallback
}

if (!$homepage_nid) {
  echo "❌ Could not find homepage.\n";
  exit(1);
}

$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "Found homepage: " . $homepage->getTitle() . " (ID: $homepage_nid)\n";

// Get current paragraphs and delete them
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  
  if (!empty($current_paragraphs)) {
    echo "Deleting " . count($current_paragraphs) . " old paragraphs...\n";
    
    foreach ($current_paragraphs as $paragraph_ref) {
      if (isset($paragraph_ref['target_id'])) {
        $old_paragraph = Paragraph::load($paragraph_ref['target_id']);
        if ($old_paragraph) {
          $old_paragraph->delete();
          echo "  - Deleted paragraph ID: " . $paragraph_ref['target_id'] . "\n";
        }
      }
    }
  }
  
  // Clear the field
  $homepage->set('field_content_sections', []);
  $homepage->save();
  
  echo "✅ Cleared all old paragraphs from homepage\n\n";
}

// Create NEW Books by Age Section paragraph
$books_by_age_paragraph = Paragraph::create([
  'type' => 'books_by_age_section',
  'field_section_title' => 'Books By Age',
  'field_section_subtitle' => 'Choose Your Category',
  'field_display_style' => 'grid',
  'field_books_per_section' => 5,
]);
$books_by_age_paragraph->save();

echo "Created new Books by Age Section paragraph (ID: " . $books_by_age_paragraph->id() . ")\n";

// Add ONLY the Books by Age section to homepage
$homepage->set('field_content_sections', [
  [
    'target_id' => $books_by_age_paragraph->id(),
    'target_revision_id' => $books_by_age_paragraph->getRevisionId(),
  ],
]);
$homepage->save();

echo "\n✅ Homepage cleaned up!\n";
echo "✅ Books by Age Section added right after hero\n";
echo "\nView at: " . $homepage->toUrl()->setAbsolute()->toString() . "\n";
