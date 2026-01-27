<?php

use Drupal\paragraphs\Entity\Paragraph;

echo "🔗 Updating Young Readers Button Link\n\n";

// Load the interested_section paragraph (ID 27 from earlier)
$paragraph_id = 27;
$paragraph = Paragraph::load($paragraph_id);

if ($paragraph) {
  echo "✓ Found interested_section paragraph (ID: $paragraph_id)\n";
  
  // Update Card 1 link to point to /books
  if ($paragraph->hasField('field_card1_link')) {
    $paragraph->set('field_card1_link', [
      'uri' => 'internal:/books',
      'title' => '',
    ]);
    $paragraph->save();
    echo "✓ Updated Young Readers button to link to /books\n";
  }
  
  echo "\n✅ Update complete!\n";
} else {
  echo "❌ Paragraph not found (ID: $paragraph_id)\n";
  echo "You may need to update this manually in the admin interface.\n";
}
