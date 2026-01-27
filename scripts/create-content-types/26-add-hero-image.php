<?php

/**
 * Add background image to hero banner
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

// Find the homepage
$query = \Drupal::entityQuery('node')
  ->condition('type', 'page')
  ->condition('title', 'Homepage')
  ->accessCheck(FALSE)
  ->range(0, 1);
$nids = $query->execute();

if (empty($nids)) {
  echo "Homepage not found!\n";
  exit;
}

$nid = reset($nids);
$node = Node::load($nid);

// Get the first paragraph (hero banner)
$paragraphs = $node->get('field_content_sections')->referencedEntities();
if (empty($paragraphs)) {
  echo "No paragraphs found!\n";
  exit;
}

$hero = $paragraphs[0];
if ($hero->bundle() !== 'hero_banner') {
  echo "First paragraph is not a hero banner!\n";
  exit;
}

echo "Found hero banner paragraph (ID: " . $hero->id() . ")\n";

// Create a placeholder image (you should replace this with the actual forest image)
// For now, we'll create a solid color placeholder
$image_url = 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1920&h=600&fit=crop';

// Download and save the image
echo "Downloading background image...\n";
$image_data = file_get_contents($image_url);

if ($image_data === FALSE) {
  echo "Failed to download image. Using local method...\n";
  // Create a local placeholder
  echo "Please upload your forest image manually to the hero banner.\n";
  echo "Go to: /node/$nid/edit\n";
  echo "Edit the Hero Banner paragraph and upload the background image.\n";
  exit;
}

// Save the image
$directory = 'public://hero-images';
$file_system = \Drupal::service('file_system');
$file_system->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

$filename = 'hero-background-' . time() . '.jpg';

// Use the file repository service (Drupal 10+)
$file_repository = \Drupal::service('file.repository');
$file = $file_repository->writeData($image_data, $directory . '/' . $filename, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);

if ($file) {
  echo "Image saved (File ID: " . $file->id() . ")\n";
  
  // Attach to hero banner
  $hero->set('field_background_image', [
    'target_id' => $file->id(),
    'alt' => 'Forest path with steps',
    'title' => 'Reading adventure',
  ]);
  $hero->save();
  
  // Resave the node to update
  $node->save();
  
  echo "✅ Background image added to hero banner!\n";
  echo "View at: /node/$nid\n";
} else {
  echo "Failed to save image file.\n";
}
