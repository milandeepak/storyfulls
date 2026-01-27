<?php

$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'book']);

echo "=== BOOKS IN DRUPAL 11 SITE ===" . PHP_EOL . PHP_EOL;
echo str_pad('ID', 5) . str_pad('Title', 50) . str_pad('Age Group', 12) . 'Cover' . PHP_EOL;
echo str_repeat('=', 90) . PHP_EOL;

foreach ($nodes as $node) {
  $has_cover = !($node->get('field_featured_image')->isEmpty()) ? '✓ Yes' : '✗ No';
  
  $age_group = 'None';
  if (!$node->get('field_age_group')->isEmpty()) {
    $age_term = $node->get('field_age_group')->entity;
    if ($age_term) {
      $age_group = $age_term->getName();
    }
  }
  
  echo str_pad($node->id(), 5);
  echo str_pad(substr($node->getTitle(), 0, 48), 50);
  echo str_pad($age_group, 12);
  echo $has_cover . PHP_EOL;
}

echo str_repeat('=', 90) . PHP_EOL;
echo PHP_EOL . '✅ Total: ' . count($nodes) . ' books imported successfully!' . PHP_EOL;
