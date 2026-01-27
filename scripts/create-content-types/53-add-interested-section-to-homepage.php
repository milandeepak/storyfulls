<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "🎯 Adding 'You Might Be Interested In' Section to Homepage\n\n";

// Find the homepage (assuming it's node ID 16)
$homepage_nid = 16;
$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "✓ Found homepage: " . $homepage->getTitle() . " (ID: $homepage_nid)\n";

// Create Interested Section paragraph
$interested_paragraph = Paragraph::create([
  'type' => 'interested_section',
  'field_section_title' => 'You Might Be Interested In',
  'field_card1_title' => 'Young Readers',
  'field_card1_description' => 'The journey of a storyteller begins with reading',
  'field_card1_button_text' => 'Discover the magic of books',
  'field_card2_title' => 'Young Writers',
  'field_card2_description' => "A space to celebrate children's literary creations",
  'field_card2_button_text' => 'Your creativity shines',
  'field_card3_title' => 'Events',
  'field_card3_description' => "Stay informed about events related to children's literature",
  'field_card3_button_text' => 'Read more',
]);
$interested_paragraph->save();

echo "✓ Created 'You Might Be Interested In' paragraph (ID: " . $interested_paragraph->id() . ")\n";

// Add to homepage's content sections field
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  
  // Find the position of the community_picks paragraph
  $community_picks_position = -1;
  foreach ($current_paragraphs as $index => $paragraph_ref) {
    $paragraph = Paragraph::load($paragraph_ref['target_id']);
    if ($paragraph && $paragraph->bundle() == 'community_picks') {
      $community_picks_position = $index;
      echo "✓ Found Community Picks section at position $index\n";
      break;
    }
  }
  
  // Insert interested section after community picks section
  if ($community_picks_position >= 0) {
    // Insert after the community picks section
    $new_paragraph_entry = [
      'target_id' => $interested_paragraph->id(),
      'target_revision_id' => $interested_paragraph->getRevisionId(),
    ];
    
    // Splice the new paragraph into the array at the right position
    array_splice($current_paragraphs, $community_picks_position + 1, 0, [$new_paragraph_entry]);
    
    echo "✓ Inserting 'You Might Be Interested In' section after Community Picks section\n";
  } else {
    // If community picks section not found, just add at the end
    echo "⚠ Community Picks section not found, adding 'You Might Be Interested In' at the end\n";
    $current_paragraphs[] = [
      'target_id' => $interested_paragraph->id(),
      'target_revision_id' => $interested_paragraph->getRevisionId(),
    ];
  }
  
  $homepage->set('field_content_sections', $current_paragraphs);
  $homepage->save();
  
  echo "\n✅ SUCCESS! 'You Might Be Interested In' section added to homepage!\n";
  echo "View at: " . $homepage->toUrl()->toString() . "\n";
} else {
  echo "❌ Homepage doesn't have field_content_sections field\n";
}
