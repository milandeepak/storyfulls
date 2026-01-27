<?php

namespace Drupal\storyfulls_young_writers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for Young Writers pages.
 */
class YoungWritersController extends ControllerBase {

  /**
   * Display the main Young Writers page.
   */
  public function main() {
    return [
      '#theme' => 'young_writers_main',
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * Display all users who have posted book reviews.
   */
  public function bookReviews() {
    // Get all users who have posted book reviews
    $database = \Drupal::database();
    
    // Query to get users with book reviews
    $query = $database->select('node_field_data', 'n')
      ->fields('n', ['uid'])
      ->condition('n.type', 'book_review')
      ->condition('n.status', 1)
      ->groupBy('n.uid');
    
    $user_ids = $query->execute()->fetchCol();
    
    // Load user data
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $users = $user_storage->loadMultiple($user_ids);
    
    $users_data = [];
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue; // Skip anonymous user
      }
      
      // Get review count for this user
      $review_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', 'book_review')
        ->condition('uid', $user->id())
        ->condition('status', 1)
        ->accessCheck(TRUE);
      
      $review_count = $review_query->count()->execute();
      
      // Get user avatar
      $user_picture = '';
      if ($user->hasField('field_avatar') && !$user->get('field_avatar')->isEmpty()) {
        $avatar_filename = $user->get('field_avatar')->value;
        $user_picture = '/themes/custom/storyfulls/images/' . $avatar_filename;
      } else {
        // Fallback to cyclical assignment based on user ID if no avatar is set
        $avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
        $avatar_index = ($user->id() - 1) % count($avatars);
        $user_picture = '/themes/custom/storyfulls/images/' . $avatars[$avatar_index];
      }
      
      $users_data[] = [
        'id' => $user->id(),
        'name' => $user->getDisplayName(),
        'picture' => $user_picture,
        'review_count' => $review_count,
      ];
    }
    
    // Sort by review count descending
    usort($users_data, function($a, $b) {
      return $b['review_count'] - $a['review_count'];
    });
    
    return [
      '#theme' => 'young_writers_book_reviews',
      '#users' => $users_data,
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * Display all reviews by a specific user.
   */
  public function userReviews(UserInterface $user) {
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
      $book_id = null;
      
      if ($review->hasField('field_reviewed_book') && !$review->get('field_reviewed_book')->isEmpty()) {
        $book = $review->get('field_reviewed_book')->entity;
        if ($book) {
          $book_id = $book->id();
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
        'book_id' => $book_id,
        'book_title' => $book_title,
        'book_cover' => $book_cover,
        'review_text' => $review_text,
        'rating' => $rating,
        'created' => $review->getCreatedTime(),
      ];
    }
    
    // Assign animal avatar cyclically based on user ID
    $avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
    $avatar_index = ($user->id() - 1) % count($avatars);
    $user_avatar = '/themes/custom/storyfulls/images/' . $avatars[$avatar_index];
    
    // Calculate user age from date of birth
    $user_age = '';
    if ($user->hasField('field_date_of_birth') && !$user->get('field_date_of_birth')->isEmpty()) {
      $dob = $user->get('field_date_of_birth')->value;
      if ($dob) {
        $birthDate = new \DateTime($dob);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;
        $user_age = $age . ' years';
      }
    }
    
    return [
      '#theme' => 'young_writers_user_reviews',
      '#user_name' => $user->getDisplayName(),
      '#user_id' => $user->id(),
      '#user_avatar' => $user_avatar,
      '#user_age' => $user_age,
      '#reviews' => $review_data,
      '#review_count' => count($review_data),
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * Display Stories & Poetry page.
   */
  public function storiesPoetry() {
    // Get all users who have posted stories or poetry
    $database = \Drupal::database();
    
    // Query to get users with story_poetry content
    $query = $database->select('node_field_data', 'n')
      ->fields('n', ['uid'])
      ->condition('n.type', 'story_poetry')
      ->condition('n.status', 1)
      ->groupBy('n.uid');
    
    $user_ids = $query->execute()->fetchCol();
    
    // Load user data
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $users = $user_storage->loadMultiple($user_ids);
    
    $users_data = [];
    $avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
    
    foreach ($users as $user) {
      if ($user->id() == 0) continue; // Skip anonymous
      
      // Get post count
      $post_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', 'story_poetry')
        ->condition('uid', $user->id())
        ->condition('status', 1)
        ->accessCheck(TRUE);
      
      $post_count = $post_query->count()->execute();
      
      // Get avatar
      $user_picture = '';
      if ($user->hasField('field_avatar') && !$user->get('field_avatar')->isEmpty()) {
        $avatar_filename = $user->get('field_avatar')->value;
        $user_picture = '/themes/custom/storyfulls/images/' . $avatar_filename;
      } else {
        $avatar_index = ($user->id() - 1) % count($avatars);
        $user_picture = '/themes/custom/storyfulls/images/' . $avatars[$avatar_index];
      }
      
      // Get Age
      $user_age = 'Age not available';
      if ($user->hasField('field_date_of_birth') && !$user->get('field_date_of_birth')->isEmpty()) {
        $dob = $user->get('field_date_of_birth')->value;
        if ($dob) {
          $birthDate = new \DateTime($dob);
          $today = new \DateTime();
          $age = $today->diff($birthDate)->y;
          $user_age = $age . ' years';
        }
      }
      
      $users_data[] = [
        'id' => $user->id(),
        'name' => $user->getDisplayName(),
        'picture' => $user_picture,
        'post_count' => $post_count,
        'age' => $user_age,
      ];
    }
    
    // Sort by post count
    usort($users_data, function($a, $b) {
      return $b['post_count'] - $a['post_count'];
    });

    return [
      '#theme' => 'young_writers_stories_poetry',
      '#writers' => $users_data,
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * Display specific user's stories and poetry.
   */
  public function userStoriesPoetry(UserInterface $user) {
    // Load stories/poetry by this user
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'story_poetry')
      ->condition('uid', $user->id())
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);
      
    $nids = $query->execute();
    $nodes = $node_storage->loadMultiple($nids);
    
    $posts = [];
    foreach ($nodes as $node) {
      $image_url = '';
      if ($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()) {
        $file = $node->get('field_featured_image')->entity;
        if ($file) {
          $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
      
      $body = '';
      if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
        $body = $node->get('body')->value; // Use raw value or processed
      }

      $posts[] = [
        'id' => $node->id(),
        'title' => $node->getTitle(),
        'image' => $image_url,
        'content' => $body,
        'created' => $node->getCreatedTime(),
      ];
    }

    // User Data for bottom section
    $avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
    $avatar_index = ($user->id() - 1) % count($avatars);
    $user_picture = '';
    
    if ($user->hasField('field_avatar') && !$user->get('field_avatar')->isEmpty()) {
       $avatar_filename = $user->get('field_avatar')->value;
       $user_picture = '/themes/custom/storyfulls/images/' . $avatar_filename;
    } else {
       $user_picture = '/themes/custom/storyfulls/images/' . $avatars[$avatar_index];
    }
    
    // Calculate Age
    $user_age = '';
    if ($user->hasField('field_date_of_birth') && !$user->get('field_date_of_birth')->isEmpty()) {
      $dob = $user->get('field_date_of_birth')->value;
      if ($dob) {
        $birthDate = new \DateTime($dob);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;
        $user_age = $age . ' years';
      }
    }

    // Check if current user is the owner
    $current_user = \Drupal::currentUser();
    $is_owner = ($current_user->id() == $user->id());

    return [
      '#theme' => 'young_writers_user_stories',
      '#user_name' => $user->getDisplayName(),
      '#user_id' => $user->id(),
      '#user_avatar' => $user_picture,
      '#user_age' => $user_age,
      '#posts' => $posts,
      '#is_owner' => $is_owner,
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * Display Junior Artists page.
   */
  public function juniorArtists() {
    return [
      '#theme' => 'young_writers_junior_artists',
      '#attached' => [
        'library' => [
          'storyfulls/young-writers',
        ],
      ],
    ];
  }

  /**
   * AJAX endpoint to get book reviewers data.
   */
  public function getBookReviewers() {
    // Get all users who have posted book reviews
    $database = \Drupal::database();
    
    // Query to get users with book reviews
    $query = $database->select('node_field_data', 'n')
      ->fields('n', ['uid'])
      ->condition('n.type', 'book_review')
      ->condition('n.status', 1)
      ->groupBy('n.uid');
    
    $user_ids = $query->execute()->fetchCol();
    
    // Load user data
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $users = $user_storage->loadMultiple($user_ids);
    
    $users_data = [];
    $avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
    $badges = ['ranger-badge.png', 'explorer-badge.png', 'master-badge.png'];
    $badge_names = ['Ranger', 'Explorer', 'Master'];
    
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue; // Skip anonymous user
      }
      
      // Get review count for this user
      $review_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', 'book_review')
        ->condition('uid', $user->id())
        ->condition('status', 1)
        ->accessCheck(TRUE);
      
      $review_count = $review_query->count()->execute();
      
      // Assign avatar cyclically based on user ID
      $avatar_index = ($user->id() - 1) % count($avatars);
      $avatar = '/themes/custom/storyfulls/images/' . $avatars[$avatar_index];
      
      // Determine badge based on review count
      $badge_index = 0; // Default to Ranger
      $badge_name = $badge_names[0];
      
      if ($review_count >= 10) {
        $badge_index = 2; // Master
        $badge_name = $badge_names[2];
      } elseif ($review_count >= 5) {
        $badge_index = 1; // Explorer
        $badge_name = $badge_names[1];
      }
      
      $badge = '/themes/custom/storyfulls/images/' . $badges[$badge_index];
      
      // Get user age from date of birth field
      $age = null;
      if ($user->hasField('field_date_of_birth') && !$user->get('field_date_of_birth')->isEmpty()) {
        $dob = $user->get('field_date_of_birth')->value;
        if ($dob) {
          $birth_date = new \DateTime($dob);
          $now = new \DateTime();
          $age = $birth_date->diff($now)->y;
        }
      }
      
      // Get user first name or display name
      $name = $user->getDisplayName();
      if ($user->hasField('field_first_name') && !$user->get('field_first_name')->isEmpty()) {
        $first_name = $user->get('field_first_name')->value;
        if ($user->hasField('field_last_name') && !$user->get('field_last_name')->isEmpty()) {
          $last_name = $user->get('field_last_name')->value;
          $name = $first_name . ' ' . $last_name;
        } else {
          $name = $first_name;
        }
      }
      
      $users_data[] = [
        'id' => $user->id(),
        'name' => $name,
        'age' => $age ? $age . ' years' : 'Age not available',
        'avatar' => $avatar,
        'badge' => $badge,
        'badge_name' => $badge_name,
        'review_count' => $review_count,
      ];
    }
    
    // Sort by review count descending
    usort($users_data, function($a, $b) {
      return $b['review_count'] - $a['review_count'];
    });
    
    return new JsonResponse($users_data);
  }

}
