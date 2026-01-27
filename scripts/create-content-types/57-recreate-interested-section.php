<?php

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "🔄 Recreating 'You Might Be Interested In' Section\n\n";

// Load the homepage
$homepage_nid = 16;
$homepage = Node::load($homepage_nid);

if (!$homepage) {
  echo "❌ Homepage node not found (ID: $homepage_nid)\n";
  exit(1);
}

echo "✓ Found homepage: " . $homepage->getTitle() . "\n";

// Remove the old interested_section paragraph if it exists
if ($homepage->hasField('field_content_sections')) {
  $current_paragraphs = $homepage->get('field_content_sections')->getValue();
  $paragraphs_to_keep = [];
  $old_interested_id = NULL;
  
  foreach ($current_paragraphs as $paragraph_ref) {
    $paragraph = Paragraph::load($paragraph_ref['target_id']);
    if ($paragraph && $paragraph->bundle() == 'interested_section') {
      $old_interested_id = $paragraph_ref['target_id'];
      echo "✓ Found old interested_section paragraph (ID: $old_interested_id), will remove\n";
    } else if ($paragraph) {
      $paragraphs_to_keep[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
  }
  
  // Create new Interested Section paragraph
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
  
  echo "✓ Created new interested_section paragraph (ID: " . $interested_paragraph->id() . ")\n";
  
  // Find community_picks position to insert after it
  $community_picks_position = -1;
  foreach ($paragraphs_to_keep as $index => $paragraph_ref) {
    $paragraph = Paragraph::load($paragraph_ref['target_id']);
    if ($paragraph && $paragraph->bundle() == 'community_picks') {
      $community_picks_position = $index;
      break;
    }
  }
  
  // Insert the new paragraph
  $new_paragraph_entry = [
    'target_id' => $interested_paragraph->id(),
    'target_revision_id' => $interested_paragraph->getRevisionId(),
  ];
  
  if ($community_picks_position >= 0) {
    array_splice($paragraphs_to_keep, $community_picks_position + 1, 0, [$new_paragraph_entry]);
    echo "✓ Inserted after Community Picks section\n";
  } else {
    $paragraphs_to_keep[] = $new_paragraph_entry;
    echo "✓ Added to end of sections\n";
  }
  
  // Update homepage
  $homepage->set('field_content_sections', $paragraphs_to_keep);
  $homepage->save();
  
  // Delete old paragraph if it exists
  if ($old_interested_id) {
    $old_paragraph = Paragraph::load($old_interested_id);
    if ($old_paragraph) {
      $old_paragraph->delete();
      echo "✓ Deleted old paragraph\n";
    }
  }
  
  echo "\n✅ SUCCESS! Section recreated\n";
}
