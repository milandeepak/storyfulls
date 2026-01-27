<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for wishlist functionality.
 */
class WishlistController extends ControllerBase {

  /**
   * Add a book to user's wishlist via AJAX.
   */
  public function addToWishlist(Request $request) {
    $current_user = \Drupal::currentUser();
    
    if (!$current_user->isAuthenticated()) {
      return new JsonResponse(['success' => false, 'message' => 'Please log in to add books to your wishlist.'], 403);
    }
    
    $book_id = $request->request->get('book_id');
    
    if (!$book_id) {
      return new JsonResponse(['success' => false, 'message' => 'Book ID is required.'], 400);
    }
    
    // Load the user
    $user = \Drupal\user\Entity\User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse(['success' => false, 'message' => 'User not found.'], 404);
    }
    
    // Check if book exists
    $book = \Drupal::entityTypeManager()->getStorage('node')->load($book_id);
    if (!$book || $book->bundle() !== 'book') {
      return new JsonResponse(['success' => false, 'message' => 'Book not found.'], 404);
    }
    
    $book_title = $book->getTitle();
    
    // Get current wishlist
    $wishlist = [];
    if ($user->hasField('field_wishlist') && !$user->get('field_wishlist')->isEmpty()) {
      foreach ($user->get('field_wishlist')->referencedEntities() as $wishlist_book) {
        $wishlist[] = $wishlist_book->id();
      }
    }
    
    // Check if book is already in wishlist
    if (in_array($book_id, $wishlist)) {
      return new JsonResponse([
        'success' => false, 
        'message' => 'This book is already in your wishlist.',
        'already_added' => true
      ], 200);
    }
    
    // Add book to wishlist
    $wishlist[] = $book_id;
    $user->set('field_wishlist', $wishlist);
    $user->save();
    
    // Invalidate user cache
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
    
    return new JsonResponse([
      'success' => true, 
      'message' => '"' . $book_title . '" has been added to your wishlist!',
      'wishlist_count' => count($wishlist)
    ]);
  }

  /**
   * Display user's wishlist page.
   */
  public function myWishlist(UserInterface $user) {
    $current_user = \Drupal::currentUser();
    
    // Check if current user can view this wishlist
    $can_view = (int)$current_user->id() === (int)$user->id() || 
                $current_user->hasPermission('administer users');
    
    if (!$can_view) {
      throw new AccessDeniedHttpException();
    }
    
    // Load wishlist books
    $wishlist_data = [];
    
    if ($user->hasField('field_wishlist') && !$user->get('field_wishlist')->isEmpty()) {
      foreach ($user->get('field_wishlist')->referencedEntities() as $book) {
        $book_cover = '';
        if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
          $cover_file = $book->get('field_featured_image')->entity;
          if ($cover_file) {
            $book_cover = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
          }
        }
        
        $author = '';
        if ($book->hasField('field_author') && !$book->get('field_author')->isEmpty()) {
          $author_terms = [];
          foreach ($book->get('field_author')->referencedEntities() as $author_term) {
            $author_terms[] = $author_term->getName();
          }
          $author = implode(', ', $author_terms);
        }
        
        $wishlist_data[] = [
          'id' => $book->id(),
          'title' => $book->getTitle(),
          'cover' => $book_cover,
          'author' => $author,
          'url' => $book->toUrl()->toString(),
        ];
      }
    }
    
    return [
      '#theme' => 'storyfulls_wishlist',
      '#user_name' => $user->getDisplayName(),
      '#user_id' => $user->id(),
      '#wishlist' => $wishlist_data,
      '#wishlist_count' => count($wishlist_data),
      '#attached' => [
        'library' => [
          'storyfulls/user-profile',
        ],
      ],
    ];
  }

  /**
   * Add a book to user's books read list via AJAX.
   */
  public function addToBooksRead(Request $request) {
    $current_user = \Drupal::currentUser();
    
    if (!$current_user->isAuthenticated()) {
      return new JsonResponse(['success' => false, 'message' => 'Please log in to add books to your reading list.'], 403);
    }
    
    $book_id = $request->request->get('book_id');
    
    if (!$book_id) {
      return new JsonResponse(['success' => false, 'message' => 'Book ID is required.'], 400);
    }
    
    // Load the user
    $user = \Drupal\user\Entity\User::load($current_user->id());
    
    if (!$user) {
      return new JsonResponse(['success' => false, 'message' => 'User not found.'], 404);
    }
    
    // Check if book exists
    $book = \Drupal::entityTypeManager()->getStorage('node')->load($book_id);
    if (!$book || $book->bundle() !== 'book') {
      return new JsonResponse(['success' => false, 'message' => 'Book not found.'], 404);
    }
    
    $book_title = $book->getTitle();
    
    // Get current books read list
    $books_read = [];
    if ($user->hasField('field_books_read') && !$user->get('field_books_read')->isEmpty()) {
      foreach ($user->get('field_books_read')->referencedEntities() as $read_book) {
        $books_read[] = $read_book->id();
      }
    }
    
    // Check if book is already in the list
    if (in_array($book_id, $books_read)) {
      return new JsonResponse([
        'success' => false, 
        'message' => 'This book is already in your reading list.',
        'already_added' => true
      ], 200);
    }
    
    // Add book to books read list
    $books_read[] = $book_id;
    $user->set('field_books_read', $books_read);
    $user->save();
    
    // Invalidate user cache
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
    
    return new JsonResponse([
      'success' => true, 
      'message' => '"' . $book_title . '" has been added to your Books I\'ve Read!',
      'books_read_count' => count($books_read)
    ]);
  }

  /**
   * Display user's books read page.
   */
  public function myBooksRead(UserInterface $user) {
    $current_user = \Drupal::currentUser();
    
    // Check if current user can view this page
    $can_view = (int)$current_user->id() === (int)$user->id() || 
                $current_user->hasPermission('administer users');
    
    if (!$can_view) {
      throw new AccessDeniedHttpException();
    }
    
    // Load books read
    $books_read_data = [];
    
    if ($user->hasField('field_books_read') && !$user->get('field_books_read')->isEmpty()) {
      foreach ($user->get('field_books_read')->referencedEntities() as $book) {
        $book_cover = '';
        if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
          $cover_file = $book->get('field_featured_image')->entity;
          if ($cover_file) {
            $book_cover = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
          }
        }
        
        $author = '';
        if ($book->hasField('field_author') && !$book->get('field_author')->isEmpty()) {
          $author_terms = [];
          foreach ($book->get('field_author')->referencedEntities() as $author_term) {
            $author_terms[] = $author_term->getName();
          }
          $author = implode(', ', $author_terms);
        }
        
        $books_read_data[] = [
          'id' => $book->id(),
          'title' => $book->getTitle(),
          'cover' => $book_cover,
          'author' => $author,
          'url' => $book->toUrl()->toString(),
        ];
      }
    }
    
    return [
      '#theme' => 'storyfulls_books_read',
      '#user_name' => $user->getDisplayName(),
      '#user_id' => $user->id(),
      '#books_read' => $books_read_data,
      '#books_read_count' => count($books_read_data),
      '#attached' => [
        'library' => [
          'storyfulls/user-profile',
        ],
      ],
    ];
  }

}
