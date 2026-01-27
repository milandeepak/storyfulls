<?php

echo "📚 Creating Sample Books\n\n";

$books = [
  [
    'title' => 'The Magical Forest Adventure',
    'description' => 'Join Emma and her forest friends on an enchanting journey through the magical woods where trees whisper secrets and animals tell tales.',
    'age_group' => ['3-5', '6-8'],
    'genre' => ['Fantasy Fiction', 'Adventure'],
    'author' => 'Sarah Johnson',
    'illustrator' => 'Maria Garcia',
    'publisher' => 'Storybook Press',
    'launch_date' => '2024-03-15',
  ],
  [
    'title' => 'Space Explorers: Journey to Mars',
    'description' => 'A thrilling science fiction adventure about a group of young astronauts who discover something extraordinary on the red planet.',
    'age_group' => ['9-12'],
    'genre' => ['Science-Fiction', 'Adventure'],
    'author' => 'Dr. James Chen',
    'publisher' => 'Future Books',
    'launch_date' => '2024-06-20',
  ],
  [
    'title' => 'The Little Dragon Who Could',
    'description' => 'A heartwarming tale about a tiny dragon who learns that being different is what makes you special.',
    'age_group' => ['0-2', '3-5'],
    'genre' => ['Picture Books', 'Fantasy Fiction'],
    'author' => 'Emily Roberts',
    'illustrator' => 'Alex Martinez',
    'publisher' => 'Rainbow Publishing',
    'launch_date' => '2024-01-10',
  ],
  [
    'title' => 'Mystery at Moonlight Manor',
    'description' => 'Three friends must solve the mystery of the strange noises coming from the old manor house before midnight.',
    'age_group' => ['9-12', '13-16'],
    'genre' => ['Mystery', 'Adventure'],
    'author' => 'Robert Thompson',
    'publisher' => 'Mystery House Books',
    'launch_date' => '2024-10-31',
  ],
  [
    'title' => 'ABC Adventures',
    'description' => 'Learn your ABCs with fun characters and colorful illustrations. Perfect for early readers!',
    'age_group' => ['0-2'],
    'genre' => ['Early Readers', 'Picture Books'],
    'author' => 'Linda Williams',
    'illustrator' => 'Tom Anderson',
    'publisher' => 'Learning Books Co',
    'launch_date' => '2023-09-01',
  ],
];

foreach ($books as $book_data) {
  // Get taxonomy terms
  $age_terms = [];
  foreach ($book_data['age_group'] as $age) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'age_group',
      'name' => $age,
    ]);
    if ($term = reset($terms)) {
      $age_terms[] = ['target_id' => $term->id()];
    }
  }
  
  $genre_terms = [];
  foreach ($book_data['genre'] as $genre) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'genere',
      'name' => $genre,
    ]);
    if (!$terms) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
        'vid' => 'genere',
        'name' => $genre,
      ]);
      $term->save();
      $genre_terms[] = ['target_id' => $term->id()];
    } else {
      $term = reset($terms);
      $genre_terms[] = ['target_id' => $term->id()];
    }
  }
  
  // Create/get author
  $author_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
    'vid' => 'author',
    'name' => $book_data['author'],
  ]);
  if (!$author_terms) {
    $author = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
      'vid' => 'author',
      'name' => $book_data['author'],
    ]);
    $author->save();
    $author_id = $author->id();
  } else {
    $author_id = reset($author_terms)->id();
  }
  
  // Create/get illustrator (if exists)
  $illustrator_id = null;
  if (isset($book_data['illustrator'])) {
    $illus_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'illustrator',
      'name' => $book_data['illustrator'],
    ]);
    if (!$illus_terms) {
      $illus = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
        'vid' => 'illustrator',
        'name' => $book_data['illustrator'],
      ]);
      $illus->save();
      $illustrator_id = $illus->id();
    } else {
      $illustrator_id = reset($illus_terms)->id();
    }
  }
  
  // Create/get publisher
  $pub_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
    'vid' => 'publisher',
    'name' => $book_data['publisher'],
  ]);
  if (!$pub_terms) {
    $pub = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
      'vid' => 'publisher',
      'name' => $book_data['publisher'],
    ]);
    $pub->save();
    $publisher_id = $pub->id();
  } else {
    $publisher_id = reset($pub_terms)->id();
  }
  
  // Create the book node
  $node = \Drupal::entityTypeManager()->getStorage('node')->create([
    'type' => 'book',
    'title' => $book_data['title'],
    'field_description' => [
      'value' => $book_data['description'],
      'format' => 'basic_html',
    ],
    'field_age_group' => $age_terms,
    'field_genere' => $genre_terms,
    'field_author' => [['target_id' => $author_id]],
    'field_illustrator' => $illustrator_id ? [['target_id' => $illustrator_id]] : [],
    'field_publisher' => [['target_id' => $publisher_id]],
    'field_launch_date' => $book_data['launch_date'],
    'status' => 1,
  ]);
  $node->save();
  
  echo "✓ Created: {$book_data['title']}\n";
}

echo "\n✅ {count($books)} sample books created!\n";
