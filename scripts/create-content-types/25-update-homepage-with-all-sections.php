<?php

/**
 * Update homepage to match Figma design with all sections
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

// Find the homepage node (assuming it's titled "Homepage")
$query = \Drupal::entityQuery('node')
  ->condition('type', 'page')
  ->condition('title', 'Homepage')
  ->accessCheck(FALSE)
  ->range(0, 1);
$nids = $query->execute();

if (empty($nids)) {
  // Create new homepage if it doesn't exist
  echo "Creating new homepage...\n";
  $node = Node::create([
    'type' => 'page',
    'title' => 'Homepage',
    'status' => 1,
    'uid' => 1,
  ]);
} else {
  // Load existing homepage
  $nid = reset($nids);
  $node = Node::load($nid);
  echo "Updating existing homepage (Node ID: $nid)...\n";
  
  // Clear existing paragraphs
  $node->set('field_content_sections', []);
}

$paragraphs = [];

// 1. HERO BANNER (already exists, update it)
echo "Adding Hero Banner section...\n";
$hero = Paragraph::create([
  'type' => 'hero_banner',
  'field_heading' => 'Ignite The Young Minds Through The Magic Of Reading!',
  'field_subheading' => 'Discover The Perfect Books Your Child Will Love',
  'field_cta_button_text' => 'Explore Our Books',
  'field_cta_button_link' => [
    'uri' => 'internal:/books',
    'title' => 'Explore Our Books',
  ],
]);
$hero->save();
$paragraphs[] = $hero;

// 2. BOOKS SHOWCASE - "Books by Age"
echo "Adding Books by Age section...\n";
$books_by_age = Paragraph::create([
  'type' => 'books_showcase',
  'field_section_title' => 'Books by Age',
  'field_filter_by_age_group' => 1,
  'field_display_style' => 'grid',
  'field_number_of_items' => 8,
]);
$books_by_age->save();
$paragraphs[] = $books_by_age;

// 3. BOOKS SHOWCASE - "Most Rated"
echo "Adding Most Rated Books section...\n";
$most_rated = Paragraph::create([
  'type' => 'books_showcase',
  'field_section_title' => 'Most Rated',
  'field_filter_by_age_group' => 0,
  'field_display_style' => 'grid',
  'field_number_of_items' => 6,
]);
$most_rated->save();
$paragraphs[] = $most_rated;

// 4. BOOKS SHOWCASE - "Books on Inclusivity 2025"
echo "Adding Books on Inclusivity section...\n";
$inclusivity = Paragraph::create([
  'type' => 'books_showcase',
  'field_section_title' => 'Books on Inclusivity 2025',
  'field_filter_by_age_group' => 0,
  'field_display_style' => 'grid',
  'field_number_of_items' => 4,
]);
$inclusivity->save();
$paragraphs[] = $inclusivity;

// 5. FEATURED CONTENT - "You Might Be Interested In" (for blogs/events)
echo "Adding Featured Content section...\n";

// Get some sample blogs/events to feature
$blog_query = \Drupal::entityQuery('node')
  ->condition('type', 'blog')
  ->condition('status', 1)
  ->accessCheck(FALSE)
  ->range(0, 3);
$blog_nids = $blog_query->execute();

$event_query = \Drupal::entityQuery('node')
  ->condition('type', 'event')
  ->condition('status', 1)
  ->accessCheck(FALSE)
  ->range(0, 3);
$event_nids = $event_query->execute();

$featured_items = array_merge($blog_nids, $event_nids);

if (!empty($featured_items)) {
  $featured = Paragraph::create([
    'type' => 'featured_content',
    'field_section_title' => 'You Might Be Interested In',
    'field_featured_items' => array_slice($featured_items, 0, 3),
    'field_display_style' => 'grid',
  ]);
  $featured->save();
  $paragraphs[] = $featured;
}

// 6. CTA - Quiz Section
echo "Adding Quiz CTA section...\n";
$quiz_cta = Paragraph::create([
  'type' => 'call_to_action',
  'field_heading' => 'Got Questions about the facts?',
  'field_body_text' => 'Test your knowledge about books and reading!',
  'field_button_text' => 'Take the Quiz',
  'field_button_link' => [
    'uri' => 'internal:/quiz',
    'title' => 'Take the Quiz',
  ],
  'field_background_color' => 'secondary',
]);
$quiz_cta->save();
$paragraphs[] = $quiz_cta;

// 7. BOOKS SHOWCASE - "Books by Genres"
echo "Adding Books by Genres section...\n";
$by_genres = Paragraph::create([
  'type' => 'books_showcase',
  'field_section_title' => 'Books by Genres',
  'field_filter_by_age_group' => 0,
  'field_display_style' => 'carousel',
  'field_number_of_items' => 8,
]);
$by_genres->save();
$paragraphs[] = $by_genres;

// 8. CTA - Book of the Season
echo "Adding Book of the Season CTA...\n";
$book_season = Paragraph::create([
  'type' => 'call_to_action',
  'field_heading' => 'Book of the Season',
  'field_body_text' => 'Discover our handpicked selection for this season',
  'field_button_text' => 'View Now',
  'field_button_link' => [
    'uri' => 'internal:/book-of-season',
    'title' => 'View Now',
  ],
  'field_background_color' => 'light',
]);
$book_season->save();
$paragraphs[] = $book_season;

// 9. Final CTA - Join Community
echo "Adding Join Community CTA...\n";
$join_cta = Paragraph::create([
  'type' => 'call_to_action',
  'field_heading' => 'Join Our Reading Community',
  'field_body_text' => 'Get personalized book recommendations, reviews, and connect with other book lovers!',
  'field_button_text' => 'Sign Up Now',
  'field_button_link' => [
    'uri' => 'internal:/user/register',
    'title' => 'Sign Up Now',
  ],
  'field_background_color' => 'gradient',
]);
$join_cta->save();
$paragraphs[] = $join_cta;

// Attach all paragraphs to the homepage
$paragraph_ids = [];
foreach ($paragraphs as $paragraph) {
  $paragraph_ids[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

$node->set('field_content_sections', $paragraph_ids);
$node->save();

echo "\n✅ SUCCESS!\n";
echo "Homepage updated with all sections from Figma design!\n";
echo "View at: /node/" . $node->id() . "\n";
echo "\nSections added:\n";
echo "1. Hero Banner\n";
echo "2. Books by Age (with age filters)\n";
echo "3. Most Rated Books\n";
echo "4. Books on Inclusivity 2025\n";
echo "5. You Might Be Interested In\n";
echo "6. Quiz CTA\n";
echo "7. Books by Genres\n";
echo "8. Book of the Season\n";
echo "9. Join Community CTA\n";
echo "\nNow set this as your homepage:\n";
echo "Go to: /admin/config/system/site-information\n";
echo "Set 'Default front page' to: /node/" . $node->id() . "\n";
