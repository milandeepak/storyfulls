<?php

echo "✍️ Creating Sample Write-ups\n\n";

$writeups = [
  [
    'title' => 'My Adventure in the Backyard',
    'body' => 'Yesterday I found a magical world in my backyard. There were tiny fairies living under the rose bush and a friendly frog who told me stories. It was the best day ever!',
    'type' => 'Story',
    'age_group' => '6-8',
  ],
  [
    'title' => 'The Moon and Stars',
    'body' => 'The moon is bright, the stars shine white, they light up the sky every night. I wish I could fly up high, and dance with the stars in the sky.',
    'type' => 'Poem',
    'age_group' => '6-8',
  ],
  [
    'title' => 'Why I Love "The Magical Forest"',
    'body' => 'I loved this book because Emma is so brave and the forest animals are funny. My favorite part was when the owl helped them find their way home. I wish I could visit the magical forest!',
    'type' => 'Book Review',
    'age_group' => '9-12',
  ],
];

foreach ($writeups as $writeup_data) {
  // Get story type
  $type_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
    'vid' => 'story_type',
    'name' => $writeup_data['type'],
  ]);
  $type_id = $type_terms ? reset($type_terms)->id() : null;
  
  // Get age group
  $age_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
    'vid' => 'age_group',
    'name' => $writeup_data['age_group'],
  ]);
  $age_id = $age_terms ? reset($age_terms)->id() : null;
  
  $node = \Drupal::entityTypeManager()->getStorage('node')->create([
    'type' => 'write_up',
    'title' => $writeup_data['title'],
    'body' => [
      'value' => $writeup_data['body'],
      'format' => 'basic_html',
    ],
    'field_story_type' => $type_id ? [['target_id' => $type_id]] : [],
    'field_age_group' => $age_id ? [['target_id' => $age_id]] : [],
    'status' => 1,
    'uid' => 1, // Admin user
  ]);
  $node->save();
  
  echo "✓ Created: {$writeup_data['title']}\n";
}

echo "\n✅ " . count($writeups) . " write-ups created!\n";
