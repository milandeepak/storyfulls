<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Controller for profile update operations.
 */
class ProfileUpdateController extends ControllerBase {

  /**
   * Update user profile.
   */
  public function updateProfile(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'User not found',
      ], 404);
    }
    
    // Get form data
    $first_name = $request->request->get('first_name');
    $last_name = $request->request->get('last_name');
    $age = $request->request->get('age');
    $currently_reading = $request->request->get('currently_reading');
    
    // Handle profile picture upload
    $picture_url = NULL;
    $files = $request->files->get('profile_picture');
    if ($files) {
      $picture_url = $this->handleFileUpload($files, $user);
    }
    
    // Update user fields
    if ($first_name) {
      $user->set('field_first_name', $first_name);
    }
    if ($last_name) {
      $user->set('field_last_name', $last_name);
    }
    if ($age) {
      $user->set('field_age', $age);
    }
    if ($currently_reading) {
      $user->set('field_currently_reading', $currently_reading);
    }
    
    $user->save();
    
    // Invalidate user cache
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
    
    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Profile updated successfully',
      'data' => [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'age' => $age,
        'currently_reading' => $currently_reading,
        'picture_url' => $picture_url,
      ],
    ]);
  }
  
  /**
   * Upload profile picture.
   */
  public function uploadPicture(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'User not found',
      ], 404);
    }
    
    $files = $request->files->get('file');
    if (!$files) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'No file uploaded',
      ], 400);
    }
    
    $picture_url = $this->handleFileUpload($files, $user);
    
    if ($picture_url) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Picture uploaded successfully',
        'data' => [
          'picture_url' => $picture_url,
        ],
      ]);
    }
    
    return new JsonResponse([
      'success' => FALSE,
      'message' => 'Failed to upload picture',
    ], 500);
  }
  
  /**
   * Update user favorites (authors, books, or genres).
   */
  public function updateFavorites(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'User not found',
      ], 404);
    }
    
    $data = json_decode($request->getContent(), TRUE);
    $type = $data['type'] ?? NULL;
    $values = $data['values'] ?? [];
    
    if (!$type || !is_array($values)) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Invalid data',
      ], 400);
    }
    
    // Determine field name and vocabulary
    $field_map = [
      'authors' => ['field' => 'field_favorite_authors', 'vocab' => 'author'],
      'books' => ['field' => 'field_favorite_books', 'vocab' => 'book_title'],
      'genres' => ['field' => 'field_favorite_genres', 'vocab' => 'genre'],
    ];
    
    if (!isset($field_map[$type])) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Invalid type',
      ], 400);
    }
    
    $field_name = $field_map[$type]['field'];
    $vocab = $field_map[$type]['vocab'];
    
    // Convert values to term IDs
    $term_ids = [];
    foreach ($values as $value) {
      if ($type === 'books') {
        // For books, we need to find the book node by title
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties([
            'type' => 'book',
            'title' => $value,
          ]);
        
        if (!empty($nodes)) {
          $node = reset($nodes);
          $term_ids[] = ['target_id' => $node->id()];
        }
      } else {
        // For authors and genres, find or create terms
        $terms = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->loadByProperties([
            'vid' => $vocab,
            'name' => $value,
          ]);
        
        if (!empty($terms)) {
          $term = reset($terms);
          $term_ids[] = ['target_id' => $term->id()];
        } else {
          // Create new term if it doesn't exist (for authors)
          if ($type === 'authors') {
            $term = Term::create([
              'vid' => $vocab,
              'name' => $value,
            ]);
            $term->save();
            $term_ids[] = ['target_id' => $term->id()];
          }
        }
      }
    }
    
    // Update user field
    $user->set($field_name, $term_ids);
    $user->save();
    
    return new JsonResponse([
      'success' => TRUE,
      'message' => ucfirst($type) . ' updated successfully',
    ]);
  }
  
  /**
   * Update week's pick.
   */
  public function updateWeeksPick(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'User not found',
      ], 404);
    }
    
    $data = json_decode($request->getContent(), TRUE);
    $book = $data['book'] ?? NULL;
    $author = $data['author'] ?? NULL;
    
    if ($book) {
      $user->set('field_weeks_pick_book', $book);
    }
    if ($author) {
      $user->set('field_weeks_pick_author', $author);
    }
    
    $user->save();
    
    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Week\'s pick updated successfully',
    ]);
  }
  
  /**
   * Update book list (wishlist or books read).
   */
  public function updateBookList(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'User not found',
      ], 404);
    }
    
    $data = json_decode($request->getContent(), TRUE);
    $type = $data['type'] ?? NULL;
    $books = $data['books'] ?? [];
    
    if (!$type || !is_array($books)) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Invalid data',
      ], 400);
    }
    
    $field_name = $type === 'wishlist' ? 'field_wishlist' : 'field_books_read';
    
    // Convert book IDs to proper format
    $book_refs = [];
    foreach ($books as $book_id) {
      $book_refs[] = ['target_id' => $book_id];
    }
    
    $user->set($field_name, $book_refs);
    $user->save();
    
    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Book list updated successfully',
    ]);
  }
  
  /**
   * Search books.
   */
  public function searchBooks(Request $request) {
    $query = $request->query->get('q');
    
    if (!$query || strlen($query) < 2) {
      return new JsonResponse([]);
    }
    
    // Search for books by title or author
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query_builder = $node_storage->getQuery()
      ->condition('type', 'book')
      ->condition('status', 1)
      ->condition('title', '%' . $query . '%', 'LIKE')
      ->range(0, 10)
      ->accessCheck(TRUE);
    
    $nids = $query_builder->execute();
    
    $results = [];
    if (!empty($nids)) {
      $nodes = $node_storage->loadMultiple($nids);
      
      foreach ($nodes as $node) {
        $cover_url = '/themes/custom/storyfulls/images/default-book-cover.jpg';
        
        if ($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()) {
          $cover_file = $node->get('field_featured_image')->entity;
          if ($cover_file) {
            $cover_url = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
          }
        }
        
        $results[] = [
          'id' => $node->id(),
          'title' => $node->getTitle(),
          'cover_url' => $cover_url,
        ];
      }
    }
    
    return new JsonResponse($results);
  }
  
  /**
   * Handle file upload.
   */
  private function handleFileUpload($file, $user) {
    // Validate file type
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
      return NULL;
    }
    
    // Create directory if it doesn't exist
    $directory = 'public://profile-pictures';
    \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
    
    // Save file
    $destination = $directory . '/' . $user->id() . '_' . time() . '.' . $file_extension;
    
    try {
      $file_content = file_get_contents($file->getPathname());
      $file_entity = \Drupal::service('file.repository')->writeData($file_content, $destination, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
      
      if ($file_entity) {
        // Update user picture field
        $user->set('user_picture', ['target_id' => $file_entity->id()]);
        $user->save();
        
        return \Drupal::service('file_url_generator')->generateAbsoluteString($file_entity->getFileUri());
      }
    } catch (\Exception $e) {
      \Drupal::logger('storyfulls_profile')->error('File upload error: @error', ['@error' => $e->getMessage()]);
    }
    
    return NULL;
  }

}
