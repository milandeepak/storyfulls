<?php

/**
 * @file
 * Setup comprehensive user profile with all required fields and content types.
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Field\BaseFieldDefinition;

// Create Book Review content type
echo "Creating Book Review content type...\n";
$book_review_type = NodeType::create([
  'type' => 'book_review',
  'name' => 'Book Review',
  'description' => 'User-submitted book reviews',
]);
$book_review_type->save();

// Add fields to Book Review
$fields_book_review = [
  'field_reviewed_book' => [
    'type' => 'entity_reference',
    'label' => 'Reviewed Book',
    'settings' => ['target_type' => 'node'],
    'cardinality' => 1,
  ],
  'field_review_text' => [
    'type' => 'text_long',
    'label' => 'Review',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_rating' => [
    'type' => 'integer',
    'label' => 'Rating',
    'settings' => ['min' => 1, 'max' => 5],
    'cardinality' => 1,
  ],
];

foreach ($fields_book_review as $field_name => $config) {
  // Create storage
  if (!FieldStorageConfig::loadByName('node', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'],
    ])->save();
  }
  
  // Create field instance
  if (!FieldConfig::loadByName('node', 'book_review', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'book_review',
      'label' => $config['label'],
      'required' => true,
    ])->save();
  }
}

echo "Book Review content type created!\n";

// Create Stories and Poetry content type
echo "Creating Stories and Poetry content type...\n";
$stories_type = NodeType::create([
  'type' => 'story_poetry',
  'name' => 'Story or Poetry',
  'description' => 'User-submitted stories and poetry',
]);
$stories_type->save();

// Add fields to Stories and Poetry
$fields_stories = [
  'field_content_type' => [
    'type' => 'list_string',
    'label' => 'Content Type',
    'settings' => [
      'allowed_values' => [
        'story' => 'Story',
        'poetry' => 'Poetry',
      ],
    ],
    'cardinality' => 1,
  ],
  'field_story_content' => [
    'type' => 'text_long',
    'label' => 'Content',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_cover_image' => [
    'type' => 'image',
    'label' => 'Cover Image',
    'settings' => [],
    'cardinality' => 1,
  ],
];

foreach ($fields_stories as $field_name => $config) {
  // Create storage
  if (!FieldStorageConfig::loadByName('node', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'],
    ])->save();
  }
  
  // Create field instance
  if (!FieldConfig::loadByName('node', 'story_poetry', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'story_poetry',
      'label' => $config['label'],
      'required' => $field_name !== 'field_cover_image',
    ])->save();
  }
}

echo "Stories and Poetry content type created!\n";

// Create Junior Artist content type
echo "Creating Junior Artist content type...\n";
$art_type = NodeType::create([
  'type' => 'junior_artist',
  'name' => 'Junior Artist',
  'description' => 'User-submitted artwork',
]);
$art_type->save();

// Add fields to Junior Artist
$fields_art = [
  'field_artwork_image' => [
    'type' => 'image',
    'label' => 'Artwork',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_artwork_description' => [
    'type' => 'text_long',
    'label' => 'Description',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_art_category' => [
    'type' => 'list_string',
    'label' => 'Category',
    'settings' => [
      'allowed_values' => [
        'illustration' => 'Illustration',
        'art_craft' => 'Art & Craft',
        'painting' => 'Painting',
        'photography' => 'Photography',
      ],
    ],
    'cardinality' => 1,
  ],
];

foreach ($fields_art as $field_name => $config) {
  // Create storage
  if (!FieldStorageConfig::loadByName('node', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'],
    ])->save();
  }
  
  // Create field instance
  if (!FieldConfig::loadByName('node', 'junior_artist', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'junior_artist',
      'label' => $config['label'],
      'required' => $field_name === 'field_artwork_image',
    ])->save();
  }
}

echo "Junior Artist content type created!\n";

// Add fields to User entity
echo "Adding fields to User profile...\n";

$user_fields = [
  'field_age' => [
    'type' => 'integer',
    'label' => 'Age',
    'settings' => ['min' => 1, 'max' => 120],
    'cardinality' => 1,
  ],
  'field_location' => [
    'type' => 'string',
    'label' => 'Location',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
  ],
  'field_managed_by_parents' => [
    'type' => 'boolean',
    'label' => 'Managed by Parents',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_bio' => [
    'type' => 'string_long',
    'label' => 'Bio/Quote',
    'settings' => [],
    'cardinality' => 1,
  ],
  'field_currently_reading' => [
    'type' => 'string',
    'label' => 'Currently Reading',
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
  ],
  'field_weeks_pick_book' => [
    'type' => 'string',
    'label' => "This Week's Pick - Book Name",
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
  ],
  'field_weeks_pick_author' => [
    'type' => 'string',
    'label' => "This Week's Pick - Author Name",
    'settings' => ['max_length' => 255],
    'cardinality' => 1,
  ],
  'field_favorite_authors' => [
    'type' => 'entity_reference',
    'label' => 'Favorite Authors',
    'settings' => ['target_type' => 'taxonomy_term'],
    'cardinality' => -1,
  ],
  'field_favorite_books' => [
    'type' => 'entity_reference',
    'label' => 'Favorite Books',
    'settings' => ['target_type' => 'node'],
    'cardinality' => -1,
  ],
  'field_favorite_genres' => [
    'type' => 'entity_reference',
    'label' => 'Favorite Genres',
    'settings' => ['target_type' => 'taxonomy_term'],
    'cardinality' => -1,
  ],
  'field_wishlist' => [
    'type' => 'entity_reference',
    'label' => 'My Wishlist (Read Later)',
    'settings' => ['target_type' => 'node'],
    'cardinality' => -1,
  ],
  'field_books_read' => [
    'type' => 'entity_reference',
    'label' => "Books I've Read",
    'settings' => ['target_type' => 'node'],
    'cardinality' => -1,
  ],
];

foreach ($user_fields as $field_name => $config) {
  // Create storage
  if (!FieldStorageConfig::loadByName('user', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'user',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'],
    ])->save();
    echo "Created storage for {$field_name}\n";
  }
  
  // Create field instance
  if (!FieldConfig::loadByName('user', 'user', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => $config['label'],
    ])->save();
    echo "Created field instance for {$field_name}\n";
  }
}

echo "\n✅ User profile setup complete!\n";
echo "\nCreated content types:\n";
echo "- Book Review (book_review)\n";
echo "- Story or Poetry (story_poetry)\n";
echo "- Junior Artist (junior_artist)\n";
echo "\nAdded user profile fields:\n";
echo "- Age, Location, Managed by Parents\n";
echo "- Bio/Quote, Currently Reading\n";
echo "- This Week's Pick (Book & Author)\n";
echo "- Favorite Authors, Books, Genres\n";
echo "- Wishlist (Read Later)\n";
echo "- Books I've Read\n";
