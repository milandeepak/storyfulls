<?php

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

echo "=== ADDING SAMPLE DATA TO 'THE GRUFFALO' ===\n\n";

// Load The Gruffalo (node 32)
$node = Node::load(32);

if (!$node) {
  echo "❌ Book not found!\n";
  exit;
}

echo "✓ Found: {$node->getTitle()}\n\n";

// Add description
$description = "A mouse took a stroll through the deep dark wood. A fox saw the mouse and the mouse looked good.\n\n";
$description .= "Walk further into the deep dark wood, and discover what happens when the quick-thinking mouse comes face to face with an owl, a snake, and a hungry Gruffalo...\n\n";
$description .= "Julia Donaldson's trademark rhyming text and Axel Scheffler's brilliant, characterful illustrations create this funny, charming tale of a mouse who outwits everyone, including the Gruffalo.";

$node->set('field_description', [
  'value' => $description,
  'format' => 'plain_text',
]);

echo "✓ Added description\n";

// Add purchase link
$node->set('field_purchase_link', [
  'uri' => 'https://www.amazon.com/Gruffalo-Julia-Donaldson/dp/0142403873',
  'title' => 'Buy on Amazon',
]);

echo "✓ Added purchase link\n";

// Find or create illustrator (Axel Scheffler)
$illustrator_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties([
    'name' => 'Axel Scheffler',
    'vid' => 'illustrator',
  ]);

if (empty($illustrator_terms)) {
  $illustrator = Term::create([
    'vid' => 'illustrator',
    'name' => 'Axel Scheffler',
  ]);
  $illustrator->save();
  echo "✓ Created illustrator: Axel Scheffler\n";
} else {
  $illustrator = reset($illustrator_terms);
  echo "✓ Found illustrator: Axel Scheffler\n";
}

$node->set('field_illustrator', ['target_id' => $illustrator->id()]);

// Add genre
$genre_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties([
    'name' => 'Picture Books',
    'vid' => 'genere',
  ]);

if (empty($genre_terms)) {
  $genre = Term::create([
    'vid' => 'genere',
    'name' => 'Picture Books',
  ]);
  $genre->save();
  echo "✓ Created genre: Picture Books\n";
} else {
  $genre = reset($genre_terms);
  echo "✓ Found genre: Picture Books\n";
}

$node->set('field_genere', ['target_id' => $genre->id()]);

// Add publisher
$publisher_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties([
    'name' => 'Macmillan Publishers',
    'vid' => 'publisher',
  ]);

if (empty($publisher_terms)) {
  $publisher = Term::create([
    'vid' => 'publisher',
    'name' => 'Macmillan Publishers',
  ]);
  $publisher->save();
  echo "✓ Created publisher: Macmillan Publishers\n";
} else {
  $publisher = reset($publisher_terms);
  echo "✓ Found publisher: Macmillan Publishers\n";
}

$node->set('field_publisher', ['target_id' => $publisher->id()]);

// Add tags
$tag_names = ['#TheGruffalo', '#JuliaDonaldson', '#AxelScheffler', '#PictureBooks'];
$tag_ids = [];

foreach ($tag_names as $tag_name) {
  $tag_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'name' => $tag_name,
      'vid' => 'tags',
    ]);
  
  if (empty($tag_terms)) {
    $tag = Term::create([
      'vid' => 'tags',
      'name' => $tag_name,
    ]);
    $tag->save();
    $tag_ids[] = ['target_id' => $tag->id()];
  } else {
    $tag = reset($tag_terms);
    $tag_ids[] = ['target_id' => $tag->id()];
  }
}

$node->set('field_tags', $tag_ids);
echo "✓ Added " . count($tag_ids) . " tags\n";

// Save the node
$node->save();

echo "\n✅ Successfully updated 'The Gruffalo' with complete metadata!\n";
echo "\nView it at: /node/32\n";
