<?php

namespace Drupal\storyfulls_profile\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Service for calculating and managing book ratings.
 */
class BookRatingService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a BookRatingService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cache = $cache;
  }

  /**
   * Calculate average rating for a book.
   *
   * @param int $book_id
   *   The book node ID.
   *
   * @return array
   *   Array containing 'average' (float), 'count' (int), and 'stars' (string).
   */
  public function calculateAverageRating($book_id) {
    // Check cache first
    $cache_key = 'book_rating:' . $book_id;
    $cached = $this->cache->get($cache_key);
    
    if ($cached) {
      return $cached->data;
    }

    // Query all reviews for this book
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'book_review')
      ->condition('field_reviewed_book', $book_id)
      ->condition('status', 1)
      ->accessCheck(TRUE);

    $review_ids = $query->execute();

    if (empty($review_ids)) {
      $result = [
        'average' => 0,
        'count' => 0,
        'stars' => '',
        'stars_filled' => 0,
      ];
      
      // Cache for 1 hour
      $this->cache->set($cache_key, $result, time() + 3600);
      return $result;
    }

    // Load reviews and calculate average
    $reviews = $this->entityTypeManager->getStorage('node')->loadMultiple($review_ids);
    $total = 0;
    $count = 0;

    foreach ($reviews as $review) {
      if ($review->hasField('field_rating') && !$review->get('field_rating')->isEmpty()) {
        $rating = $review->get('field_rating')->value;
        if ($rating > 0) {
          $total += $rating;
          $count++;
        }
      }
    }

    $average = $count > 0 ? round($total / $count, 1) : 0;
    $stars_filled = $count > 0 ? round($average) : 0;
    
    // Generate star display
    $stars = $this->generateStarDisplay($average);

    $result = [
      'average' => $average,
      'count' => $count,
      'stars' => $stars,
      'stars_filled' => $stars_filled,
    ];

    // Cache for 1 hour
    $this->cache->set($cache_key, $result, time() + 3600);

    return $result;
  }

  /**
   * Get review count for a book.
   *
   * @param int $book_id
   *   The book node ID.
   *
   * @return int
   *   Number of reviews.
   */
  public function getReviewCount($book_id) {
    $rating_data = $this->calculateAverageRating($book_id);
    return $rating_data['count'];
  }

  /**
   * Check if a user has already reviewed a book.
   *
   * @param int $book_id
   *   The book node ID.
   * @param int $user_id
   *   The user ID.
   *
   * @return int|null
   *   The review node ID if found, NULL otherwise.
   */
  public function getUserReview($book_id, $user_id) {
    if (!$book_id || !$user_id) {
      return NULL;
    }

    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'book_review')
      ->condition('uid', $user_id)
      ->condition('field_reviewed_book', $book_id)
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->range(0, 1);

    $result = $query->execute();
    
    return !empty($result) ? reset($result) : NULL;
  }

  /**
   * Invalidate cached rating for a book.
   *
   * @param int $book_id
   *   The book node ID.
   */
  public function invalidateRatingCache($book_id) {
    $cache_key = 'book_rating:' . $book_id;
    $this->cache->delete($cache_key);
  }

  /**
   * Generate star display string.
   *
   * @param float $rating
   *   The rating value (0-5).
   *
   * @return string
   *   Star display string (e.g., "★★★★☆").
   */
  protected function generateStarDisplay($rating) {
    $filled = round($rating);
    $empty = 5 - $filled;
    
    return str_repeat('★', $filled) . str_repeat('☆', $empty);
  }

  /**
   * Get top rated books.
   *
   * @param int $limit
   *   Number of books to return.
   * @param int $min_reviews
   *   Minimum number of reviews required.
   *
   * @return array
   *   Array of book IDs sorted by average rating.
   */
  public function getTopRatedBooks($limit = 10, $min_reviews = 3) {
    // Get all books with reviews
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'book')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    $book_ids = $query->execute();

    if (empty($book_ids)) {
      return [];
    }

    // Calculate ratings for each book
    $book_ratings = [];
    foreach ($book_ids as $book_id) {
      $rating_data = $this->calculateAverageRating($book_id);
      
      // Only include books with minimum reviews
      if ($rating_data['count'] >= $min_reviews) {
        $book_ratings[$book_id] = $rating_data['average'];
      }
    }

    // Sort by rating (descending)
    arsort($book_ratings);

    // Return top N book IDs
    return array_slice(array_keys($book_ratings), 0, $limit, TRUE);
  }

}
