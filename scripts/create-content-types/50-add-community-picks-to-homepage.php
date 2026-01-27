<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "🌟 Adding Community Picks Section to Homepage\n\n";

// Find the homepage (assuming it's node ID 16)
$homepage_nid = 16;
$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "✓ Found homepage: " . $homepage->getTitle() . " (ID: $homepage_nid)\n";

// Create Community Picks paragraph
$community_picks_paragraph = Paragraph::create([
  'type' => 'community_picks',
  'field_section_title' => 'Discover books handpicked by a community of parents & bookworms.',
  'field_number_of_items' => 4,
  'field_display_style' => 'grid',
]);
$community_picks_paragraph->save();

echo "✓ Created Community Picks paragraph (ID: " . $community_picks_paragraph->id() . ")\n";

// Add to homepage's content sections field
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  
  // Find the position of the books_by_age_section paragraph
  $books_by_age_position = -1;
  foreach ($current_paragraphs as $index => $paragraph_ref) {
    $paragraph = Paragraph::load($paragraph_ref['target_id']);
    if ($paragraph && $paragraph->bundle() == 'books_by_age_section') {
      $books_by_age_position = $index;
      echo "✓ Found Books by Age section at position $index\n";
      break;
    }
  }
  
  // Insert community picks after books by age section
  if ($books_by_age_position >= 0) {
    // Insert after the books by age section
    $new_paragraph_entry = [
      'target_id' => $community_picks_paragraph->id(),
      'target_revision_id' => $community_picks_paragraph->getRevisionId(),
    ];
    
    // Splice the new paragraph into the array at the right position
    array_splice($current_paragraphs, $books_by_age_position + 1, 0, [$new_paragraph_entry]);
    
    echo "✓ Inserting Community Picks section after Books by Age section\n";
  } else {
    // If books by age section not found, just add at the end
    echo "⚠ Books by Age section not found, adding Community Picks at the end\n";
    $current_paragraphs[] = [
      'target_id' => $community_picks_paragraph->id(),
      'target_revision_id' => $community_picks_paragraph->getRevisionId(),
    ];
  }
  
  $homepage->set('field_content_sections', $current_paragraphs);
  $homepage->save();
  
  echo "\n✅ SUCCESS! Community Picks section added to homepage!\n";
  echo "View at: " . $homepage->toUrl()->toString() . "\n";
} else {
  echo "❌ Homepage doesn't have field_content_sections field\n";
}
