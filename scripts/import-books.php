<?php

/**
 * Import books from migrated JSON data into Drupal 11
 * 
 * This script will:
 * 1. Delete existing test books
 * 2. Import all books from JSON
 * 3. Create all necessary taxonomy terms (authors, illustrators, genres, publishers)
 * 4. Map all fields correctly
 * 5. Set up URL aliases matching old site format
 */

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\path_alias\Entity\PathAlias;

// Configuration
define('JSON_FILE', '/var/www/html/scripts/migrated_books.json');
define('IMAGE_SOURCE_DIR', '/var/www/html/web/themes/custom/storyfulls/images/books');

echo str_repeat("=", 70) . "\n";
echo "STORYFULLS BOOK IMPORT - DRUPAL 11\n";
echo str_repeat("=", 70) . "\n\n";

// Step 1: Delete existing test books
echo "STEP 1: Deleting existing test books...\n";
$query = \Drupal::entityQuery('node')
  ->accessCheck(TRUE)
  ->condition('type', 'book');

$nids = $query->execute();

if (!empty($nids)) {
  $nodes = Node::loadMultiple($nids);
  foreach ($nodes as $node) {
    $node->delete();
  }
  echo "  ✓ Deleted " . count($nids) . " existing books\n";
} else {
  echo "  ✓ No existing books to delete\n";
}

// Clear caches
drupal_flush_all_caches();
echo "  ✓ Caches cleared\n\n";

// Step 2: Load JSON data
echo "STEP 2: Loading migrated book data...\n";
if (!file_exists(JSON_FILE)) {
  echo "  ✗ ERROR: JSON file not found at " . JSON_FILE . "\n";
  exit(1);
}

$json = file_get_contents(JSON_FILE);
$books_data = json_decode($json, TRUE);

if (empty($books_data)) {
  echo "  ✗ ERROR: No books found in JSON file\n";
  exit(1);
}

echo "  ✓ Loaded " . count($books_data) . " books from JSON\n\n";

// Helper functions
class BookImporter {
  private $authorTerms = [];
  private $illustratorTerms = [];
  private $publisherTerms = [];
  private $genreTerms = [];
  private $ageGroupTerms = [];
  private $stats = [
    'imported' => 0,
    'failed' => 0,
    'skipped' => 0,
  ];

  /**
   * Get or create a taxonomy term
   */
  private function getOrCreateTerm($name, $vocabulary) {
    if (empty($name)) {
      return null;
    }

    $name = trim($name);
    
    // Check cache first
    $cacheKey = $vocabulary . '_' . $name;
    if (isset($this->{"${vocabulary}Terms"}[$cacheKey])) {
      return $this->{"${vocabulary}Terms"}[$cacheKey];
    }

    // Search for existing term
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $name,
        'vid' => $vocabulary,
      ]);

    if (!empty($terms)) {
      $term = reset($terms);
      $this->{"${vocabulary}Terms"}[$cacheKey] = $term->id();
      return $term->id();
    }

    // Create new term
    $term = Term::create([
      'name' => $name,
      'vid' => $vocabulary,
    ]);
    $term->save();

    $this->{"${vocabulary}Terms"}[$cacheKey] = $term->id();
    return $term->id();
  }

  /**
   * Map age group from old site to new site format
   */
  private function mapAgeGroup($ageString) {
    if (empty($ageString)) {
      return null;
    }

    // Extract first age group if multiple
    $ages = explode(',', $ageString);
    $age = trim($ages[0]);

    // Map age groups
    $mapping = [
      '0-2' => '0-2',
      '3-5' => '3-5',
      '2-5' => '3-5',
      '6-8' => '6-8',
      '5-8' => '6-8',
      '9-12' => '9-12',
      '8-12' => '9-12',
      '13-16' => '13-16',
    ];

    $mappedAge = isset($mapping[$age]) ? $mapping[$age] : $age;
    
    return $this->getOrCreateTerm($mappedAge, 'age_group');
  }

  /**
   * Upload image file
   */
  private function uploadImage($imageFilename, $bookId) {
    if (empty($imageFilename)) {
      return null;
    }

    $sourcePath = IMAGE_SOURCE_DIR . '/' . $imageFilename;
    
    if (!file_exists($sourcePath)) {
      echo "    ⚠ Image not found: {$imageFilename}\n";
      return null;
    }

    // Create public directory if needed
    $directory = 'public://book_covers';
    if (!\Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY)) {
      echo "    ⚠ Could not create directory: {$directory}\n";
      return null;
    }

    // Copy file to public directory
    $destination = $directory . '/' . $imageFilename;
    
    try {
      $file_data = file_get_contents($sourcePath);
      $file = \Drupal::service('file.repository')->writeData($file_data, $destination, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
      
      if ($file) {
        return $file->id();
      }
    } catch (\Exception $e) {
      echo "    ⚠ Failed to upload image: " . $e->getMessage() . "\n";
    }

    return null;
  }

  /**
   * Import a single book
   */
  public function importBook($bookData) {
    $oldId = $bookData['old_id'];
    $title = $bookData['title'];

    if (empty($title)) {
      echo "  [Book {$oldId}] ✗ Skipped - no title\n";
      $this->stats['skipped']++;
      return;
    }

    echo "  [Book {$oldId}] Importing: {$title}\n";

    try {
      // Prepare field values
      $nodeData = [
        'type' => 'book',
        'title' => $title,
        'status' => 1, // Published
        'uid' => 1, // Admin user
      ];

      // Description
      if (!empty($bookData['description'])) {
        $nodeData['field_description'] = [
          'value' => $bookData['description'],
          'format' => 'full_html',
        ];
      }

      // Cover image
      if (!empty($bookData['cover_image'])) {
        $fid = $this->uploadImage($bookData['cover_image'], $oldId);
        if ($fid) {
          $nodeData['field_featured_image'] = [
            'target_id' => $fid,
            'alt' => $title,
            'title' => $title,
          ];
          echo "    ✓ Image uploaded\n";
        }
      }

      // Authors
      if (!empty($bookData['author'])) {
        $authorIds = [];
        foreach ($bookData['author'] as $authorName) {
          $tid = $this->getOrCreateTerm($authorName, 'author');
          if ($tid) {
            $authorIds[] = ['target_id' => $tid];
          }
        }
        if (!empty($authorIds)) {
          $nodeData['field_author'] = $authorIds;
          echo "    ✓ Authors: " . implode(', ', $bookData['author']) . "\n";
        }
      }

      // Illustrators
      if (!empty($bookData['illustrator'])) {
        $illustratorIds = [];
        foreach ($bookData['illustrator'] as $illustratorName) {
          $tid = $this->getOrCreateTerm($illustratorName, 'illustrator');
          if ($tid) {
            $illustratorIds[] = ['target_id' => $tid];
          }
        }
        if (!empty($illustratorIds)) {
          $nodeData['field_illustrator'] = $illustratorIds;
          echo "    ✓ Illustrators: " . implode(', ', $bookData['illustrator']) . "\n";
        }
      }

      // Publisher
      if (!empty($bookData['publisher'])) {
        $tid = $this->getOrCreateTerm($bookData['publisher'], 'publisher');
        if ($tid) {
          $nodeData['field_publisher'] = [['target_id' => $tid]];
          echo "    ✓ Publisher: {$bookData['publisher']}\n";
        }
      }

      // Age group
      if (!empty($bookData['age_group'])) {
        $tid = $this->mapAgeGroup($bookData['age_group']);
        if ($tid) {
          $nodeData['field_age_group'] = [['target_id' => $tid]];
          echo "    ✓ Age group: {$bookData['age_group']}\n";
        }
      }

      // Genres
      if (!empty($bookData['genres'])) {
        $genreIds = [];
        foreach ($bookData['genres'] as $genreName) {
          $tid = $this->getOrCreateTerm($genreName, 'genere');
          if ($tid) {
            $genreIds[] = ['target_id' => $tid];
          }
        }
        if (!empty($genreIds)) {
          $nodeData['field_genere'] = $genreIds;
          echo "    ✓ Genres: " . implode(', ', $bookData['genres']) . "\n";
        }
      }

      // ISBN
      if (!empty($bookData['isbn'])) {
        $nodeData['field_isbn'] = $bookData['isbn'];
      }

      // Create the node
      $node = Node::create($nodeData);
      $node->save();

      $nid = $node->id();
      echo "    ✓ Book created (Node ID: {$nid})\n";

      // Create URL alias: /book/{old_id}
      $existing_aliases = \Drupal::entityTypeManager()
        ->getStorage('path_alias')
        ->loadByProperties([
          'path' => '/node/' . $nid,
        ]);

      foreach ($existing_aliases as $alias) {
        $alias->delete();
      }

      $alias = PathAlias::create([
        'path' => '/node/' . $nid,
        'alias' => '/book/' . $oldId,
        'langcode' => 'en',
      ]);
      $alias->save();

      echo "    ✓ URL alias created: /book/{$oldId}\n";
      echo "    ✓ SUCCESS!\n\n";

      $this->stats['imported']++;

    } catch (\Exception $e) {
      echo "    ✗ FAILED: " . $e->getMessage() . "\n\n";
      $this->stats['failed']++;
    }
  }

  /**
   * Get import statistics
   */
  public function getStats() {
    return $this->stats;
  }
}

// Step 3: Import all books
echo "STEP 3: Importing books into Drupal...\n\n";

$importer = new BookImporter();
$total = count($books_data);

foreach ($books_data as $index => $bookData) {
  $num = $index + 1;
  echo "[{$num}/{$total}] ";
  $importer->importBook($bookData);
  
  // Progress update every 50 books
  if ($num % 50 == 0) {
    $stats = $importer->getStats();
    echo "  >>> Progress: {$stats['imported']} imported, {$stats['failed']} failed, {$stats['skipped']} skipped\n\n";
  }
}

// Final statistics
echo "\n" . str_repeat("=", 70) . "\n";
echo "IMPORT COMPLETE!\n";
echo str_repeat("=", 70) . "\n";

$stats = $importer->getStats();
echo "Total books processed: {$total}\n";
echo "  ✓ Successfully imported: {$stats['imported']}\n";
echo "  ✗ Failed: {$stats['failed']}\n";
echo "  ⊘ Skipped: {$stats['skipped']}\n";
echo "\n";

echo "All books are now available at URLs like: /book/{old_id}\n";
echo "Example: /book/2011\n";
echo "\n";

// Clear caches one final time
drupal_flush_all_caches();
echo "✓ Final cache clear complete\n";
echo str_repeat("=", 70) . "\n";
