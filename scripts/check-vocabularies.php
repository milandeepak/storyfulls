<?php

/**
 * Quick script to check what vocabularies exist
 */

use Drupal\taxonomy\Entity\Vocabulary;

echo "Checking existing vocabularies...\n\n";

$vocabularies = Vocabulary::loadMultiple();

if (empty($vocabularies)) {
  echo "No vocabularies found!\n";
} else {
  echo "Found " . count($vocabularies) . " vocabularies:\n\n";
  foreach ($vocabularies as $vocab) {
    echo "- Machine name: " . $vocab->id() . "\n";
    echo "  Label: " . $vocab->label() . "\n\n";
  }
}

// Also check for terms in vocabularies we expect
$expected_vocabs = ['genere', 'genre', 'age_group', 'authors', 'illustrator', 'publisher'];

echo "\nChecking term counts in expected vocabularies:\n\n";

foreach ($expected_vocabs as $vid) {
  $query = \Drupal::entityQuery('taxonomy_term')
    ->condition('vid', $vid)
    ->accessCheck(TRUE)
    ->count();
  
  try {
    $count = $query->execute();
    if ($count > 0) {
      echo "✓ $vid: $count terms\n";
    } else {
      echo "  $vid: 0 terms (might not exist)\n";
    }
  } catch (\Exception $e) {
    echo "  $vid: Error - " . $e->getMessage() . "\n";
  }
}

echo "\n";
