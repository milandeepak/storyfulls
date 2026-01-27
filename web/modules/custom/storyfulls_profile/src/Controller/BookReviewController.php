<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for book review pages.
 */
class BookReviewController extends ControllerBase {

  /**
   * Display user's book reviews.
   */
  public function myReviews(UserInterface $user) {
    // Allow public access to view any user's book reviews
    
    // Load all book reviews by this user
    $review_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $review_storage->getQuery()
      ->condition('type', 'book_review')
      ->condition('uid', $user->id())
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);
    
    $review_ids = $query->execute();
    $reviews = $review_storage->loadMultiple($review_ids);
    
    // Prepare review data
    $review_data = [];
    foreach ($reviews as $review) {
      $book = null;
      $book_cover = '';
      $book_title = 'Unknown Book';
      
      if ($review->hasField('field_reviewed_book') && !$review->get('field_reviewed_book')->isEmpty()) {
        $book = $review->get('field_reviewed_book')->entity;
        if ($book) {
          $book_title = $book->getTitle();
          
          // Get book cover
          if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
            $cover_file = $book->get('field_featured_image')->entity;
            if ($cover_file) {
              $book_cover = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
            }
          }
        }
      }
      
      $review_text = '';
      if ($review->hasField('field_review_text') && !$review->get('field_review_text')->isEmpty()) {
        $review_text = $review->get('field_review_text')->value;
      }
      
      $rating = 0;
      if ($review->hasField('field_rating') && !$review->get('field_rating')->isEmpty()) {
        $rating = $review->get('field_rating')->value;
      }
      
      $review_data[] = [
        'id' => $review->id(),
        'book_id' => $book ? $book->id() : null,
        'book_title' => $book_title,
        'book_cover' => $book_cover,
        'review_text' => $review_text,
        'rating' => $rating,
        'created' => $review->getCreatedTime(),
      ];
    }
    
    // Return themed output
    return [
      '#theme' => 'storyfulls_book_reviews',
      '#user_name' => $user->getDisplayName(),
      '#user_id' => $user->id(),
      '#reviews' => $review_data,
      '#review_count' => count($review_data),
      '#attached' => [
        'library' => [
          'storyfulls/user-profile',
        ],
      ],
    ];
  }

  /**
   * Display the write review form.
   */
  public function writeReview(NodeInterface $book) {
    // Check if the book node is of type 'book'
    if ($book->bundle() !== 'book') {
      throw new NotFoundHttpException();
    }

    // Get book details
    $book_title = $book->getTitle();
    $book_cover = '';
    
    // Get book cover
    if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
      $cover_file = $book->get('field_featured_image')->entity;
      if ($cover_file) {
        $book_cover = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
      }
    }

    // Get book author
    $book_author = '';
    if ($book->hasField('field_author') && !$book->get('field_author')->isEmpty()) {
      $author_entity = $book->get('field_author')->entity;
      if ($author_entity) {
        $book_author = $author_entity->label();
      }
    }

    // Return themed output
    return [
      '#theme' => 'storyfulls_write_review',
      '#book_id' => $book->id(),
      '#book_title' => $book_title,
      '#book_author' => $book_author,
      '#book_cover' => $book_cover,
      '#attached' => [
        'library' => [
          'storyfulls/write-review',
        ],
      ],
    ];
  }

  /**
   * Submit a book review.
   */
  public function submitReview(NodeInterface $book, Request $request) {
    // Check if the book node is of type 'book'
    if ($book->bundle() !== 'book') {
      throw new NotFoundHttpException();
    }

    // Get current user
    $current_user = \Drupal::currentUser();
    
    // Get form data
    $rating = $request->request->get('rating');
    $review_text = $request->request->get('review_text');

    // Validate
    if (empty($rating) || empty($review_text)) {
      \Drupal::messenger()->addError($this->t('Please provide both a rating and review text.'));
      $url = Url::fromRoute('storyfulls_profile.write_review', ['book' => $book->id()])->toString();
      return new RedirectResponse($url);
    }

    // Create book review node
    try {
      $review_node = \Drupal::entityTypeManager()->getStorage('node')->create([
        'type' => 'book_review',
        'title' => $this->t('Review of @book by @user', [
          '@book' => $book->getTitle(),
          '@user' => $current_user->getDisplayName(),
        ]),
        'uid' => $current_user->id(),
        'status' => 1,
        'field_reviewed_book' => ['target_id' => $book->id()],
        'field_rating' => ['value' => $rating],
        'field_review_text' => ['value' => $review_text, 'format' => 'basic_html'],
      ]);
      $review_node->save();

      \Drupal::messenger()->addStatus($this->t('Thank you for your review!'));
      
      // Redirect to book page using canonical URL (respects aliases)
      $url = $book->toUrl('canonical')->toString();
      return new RedirectResponse($url);
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('There was an error submitting your review. Please try again.'));
      \Drupal::logger('storyfulls_profile')->error('Error creating review: @error', ['@error' => $e->getMessage()]);
      $url = Url::fromRoute('storyfulls_profile.write_review', ['book' => $book->id()])->toString();
      return new RedirectResponse($url);
    }
  }

}
