<?php

echo "📅 Creating Sample Events\n\n";

$events = [
  [
    'title' => 'Story Time at Central Library',
    'short_description' => 'Join us for an enchanting story time session for kids ages 3-5.',
    'body' => 'Bring your little ones for a magical morning of stories, songs, and fun! Professional storyteller Maria will bring beloved picture books to life.',
    'event_date' => '2024-02-15T10:00:00',
    'location' => 'Central Library, Main Reading Room',
    'age_group' => ['3-5'],
  ],
  [
    'title' => 'Meet the Author: Sarah Johnson',
    'short_description' => 'Book signing and Q&A with the author of The Magical Forest Adventure.',
    'body' => 'Meet Sarah Johnson in person! Get your books signed, ask questions, and hear about her upcoming projects.',
    'event_date' => '2024-03-20T14:00:00',
    'end_date' => '2024-03-20T16:00:00',
    'location' => 'Storybook Cafe, Downtown',
    'age_group' => ['3-5', '6-8'],
    'url' => 'https://example.com/register',
  ],
  [
    'title' => 'Summer Reading Challenge Kickoff',
    'short_description' => 'Start your summer reading adventure with games, prizes, and fun!',
    'body' => 'Launch your summer reading with us! Register for our reading challenge, enjoy activities, and win prizes.',
    'event_date' => '2024-06-01T11:00:00',
    'location' => 'Main Street Park',
    'age_group' => ['0-2', '3-5', '6-8', '9-12'],
  ],
];

foreach ($events as $event_data) {
  // Get age groups
  $age_terms = [];
  foreach ($event_data['age_group'] as $age) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'age_group',
      'name' => $age,
    ]);
    if ($term = reset($terms)) {
      $age_terms[] = ['target_id' => $term->id()];
    }
  }
  
  $node = \Drupal::entityTypeManager()->getStorage('node')->create([
    'type' => 'event',
    'title' => $event_data['title'],
    'field_short_description' => [
      'value' => $event_data['short_description'],
      'format' => 'basic_html',
    ],
    'body' => [
      'value' => $event_data['body'],
      'format' => 'basic_html',
    ],
    'field_event_date' => $event_data['event_date'],
    'field_end_date' => $event_data['end_date'] ?? null,
    'field_event_location' => $event_data['location'],
    'field_url' => isset($event_data['url']) ? [
      'uri' => $event_data['url'],
      'title' => 'Register',
    ] : [],
    'field_age_group' => $age_terms,
    'status' => 1,
  ]);
  $node->save();
  
  echo "✓ Created: {$event_data['title']}\n";
}

echo "\n✅ " . count($events) . " events created!\n";
