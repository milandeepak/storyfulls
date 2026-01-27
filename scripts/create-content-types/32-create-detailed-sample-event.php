<?php

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\media\Entity\Media;

echo "🚀 Creating Detailed Sample Event...\n";

// 1. Create Tags
$tags = ['BlrLitFest', 'BookClub', 'Reading'];
$tag_ids = [];

foreach ($tags as $tag_name) {
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $tag_name, 'vid' => 'tags']);
  if (empty($terms)) {
    $term = Term::create([
      'vid' => 'tags',
      'name' => $tag_name,
    ]);
    $term->save();
    $tag_ids[] = $term->id();
    echo "  - Created tag: $tag_name\n";
  } else {
    $tag_ids[] = reset($terms)->id();
  }
}

// 2. Create Age Group
$age_group_name = 'Above 2 yr';
$terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $age_group_name, 'vid' => 'age_group']);
if (empty($terms)) {
  $term = Term::create([
    'vid' => 'age_group',
    'name' => $age_group_name,
  ]);
  $term->save();
  $age_term_id = $term->id();
} else {
  $age_term_id = reset($terms)->id();
}

// 3. Create Event Node
$node = Node::create([
  'type' => 'event',
  'title' => 'IBBY International Children\'s Book Day 2025',
  'status' => 1,
  'field_event_location' => 'Virtual( online )',
  'field_event_date' => '2025-04-02T10:00:00',
  'field_end_date' => '2025-04-02T16:00:00',
  'field_url' => [
    'uri' => 'https://tinyurl.com/mwpe44wp',
    'title' => 'https://tinyurl.com/mwpe44wp'
  ],
  'field_age_group' => ['target_id' => $age_term_id],
  'field_tags' => array_map(fn($id) => ['target_id' => $id], $tag_ids),
  'field_short_description' => "Expands Vocabulary\nBoosts Reading Enthusiasm",
  'body' => [
    'value' => "Since 1967, International Children's Book Day (ICBD) has been celebrated worldwide on or around April 2, the date of Hans Christian Andersen's birthday. This special day was established by IBBY to promote the love of reading and to inspire people around the world to celebrate children's books.\n\nEach year, a different national section of IBBY is given the opportunity to be the sponsor of ICBD. The section chooses a theme and invites a prominent author from the host country to write a message to the children of the world and a well-known illustrator to design a poster.\n\nInternational drawing competition\n\nMake a drawing, painting or some other kind of art based on the poem 'The Language of Pictures'. Then take a photograph of your art and (optionally) a photograph of yourself with your artwork and email it to your national IBBY section or directly to IBBY-Netherlands: ibby.secretariaat@gmail.com.\n\nAll the children around the world who are taking part in International Children's Book Day are invited to join in! IBBY-Netherlands will show all the entries on their website and select the winners, who will be rewarded with a package of books for their school. This virtual exhibition is our way of celebrating the freedom of imagination.",
    'format' => 'basic_html',
  ],
]);

// Handle Image if available in creating path, otherwise skip or try to find one
// For now we assume the preprocess hook handles the default image if missing
// But let's try to copy the dummy one we used before if it exists
/*
$image_path = 'public://event-sample.jpg';
if (file_exists('/var/www/html/themes/custom/storyfulls/images/eventcardimage.png')) {
    // copy to public // This path is tricky in DDEV vs local script
}
*/

$node->save();

echo "✅ Created Event: " . $node->getTitle() . " (Node ID: " . $node->id() . ")\n";
echo "👉 View at: /node/" . $node->id() . "\n";
