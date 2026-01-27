<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;

/**
 * Controller for editing favorites pages.
 */
class EditFavoritesController extends ControllerBase {

  /**
   * Edit Favorite Authors page.
   */
  public function editAuthors(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }
    
    // Handle form submission
    if ($request->isMethod('POST')) {
      return $this->handleAuthorsSubmit($request, $user);
    }
    
    // Get current favorite authors
    $favorite_authors = [];
    if ($user->hasField('field_favorite_authors') && !$user->get('field_favorite_authors')->isEmpty()) {
      foreach ($user->get('field_favorite_authors') as $item) {
        if ($item->entity) {
          $favorite_authors[] = [
            'id' => $item->target_id,
            'name' => $item->entity->getName(),
          ];
        }
      }
    }
    
    return [
      '#theme' => 'storyfulls_edit_favorite_authors',
      '#user_id' => $user->id(),
      '#favorite_authors' => $favorite_authors,
      '#attached' => [
        'library' => [
          'storyfulls/edit-favorites',
        ],
      ],
    ];
  }

  /**
   * Edit Favorite Books page.
   */
  public function editBooks(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }
    
    // Handle form submission
    if ($request->isMethod('POST')) {
      return $this->handleBooksSubmit($request, $user);
    }
    
    // Get current favorite books
    $favorite_books = [];
    if ($user->hasField('field_favorite_books') && !$user->get('field_favorite_books')->isEmpty()) {
      foreach ($user->get('field_favorite_books') as $item) {
        if ($item->entity) {
          $book = $item->entity;
          $cover_url = NULL;
          
          if ($book->hasField('field_featured_image') && !$book->get('field_featured_image')->isEmpty()) {
            $cover_file = $book->get('field_featured_image')->entity;
            if ($cover_file) {
              $cover_url = \Drupal::service('file_url_generator')->generateAbsoluteString($cover_file->getFileUri());
            }
          }
          
          $favorite_books[] = [
            'id' => $book->id(),
            'title' => $book->getTitle(),
            'cover_url' => $cover_url,
          ];
        }
      }
    }
    
    return [
      '#theme' => 'storyfulls_edit_favorite_books',
      '#user_id' => $user->id(),
      '#favorite_books' => $favorite_books,
      '#attached' => [
        'library' => [
          'storyfulls/edit-favorites',
        ],
      ],
    ];
  }

  /**
   * Edit Favorite Genres page.
   */
  public function editGenres(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }
    
    // Handle form submission
    if ($request->isMethod('POST')) {
      return $this->handleGenresSubmit($request, $user);
    }
    
    // Get current favorite genres
    $favorite_genres = [];
    if ($user->hasField('field_favorite_genres') && !$user->get('field_favorite_genres')->isEmpty()) {
      foreach ($user->get('field_favorite_genres') as $item) {
        if ($item->entity) {
          $favorite_genres[] = [
            'id' => $item->target_id,
            'name' => $item->entity->getName(),
          ];
        }
      }
    }
    
    // Get all available genres
    $all_genres = [];
    $genre_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'genre']);
    
    foreach ($genre_terms as $term) {
      $all_genres[] = [
        'id' => $term->id(),
        'name' => $term->getName(),
      ];
    }
    
    return [
      '#theme' => 'storyfulls_edit_favorite_genres',
      '#user_id' => $user->id(),
      '#favorite_genres' => $favorite_genres,
      '#all_genres' => $all_genres,
      '#attached' => [
        'library' => [
          'storyfulls/edit-favorites',
        ],
      ],
    ];
  }

  /**
   * Handle authors form submission.
   */
  private function handleAuthorsSubmit(Request $request, User $user) {
    $authors_json = $request->request->get('authors_data');
    
    if ($authors_json) {
      $authors = json_decode($authors_json, TRUE);
      $term_ids = [];
      
      foreach ($authors as $author_name) {
        if (!empty($author_name)) {
          // Try to find existing author
          $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties([
              'vid' => 'author',
              'name' => $author_name,
            ]);
          
          if (!empty($terms)) {
            $term = reset($terms);
            $term_ids[] = ['target_id' => $term->id()];
          } else {
            // Create new author
            $term = Term::create([
              'vid' => 'author',
              'name' => $author_name,
            ]);
            $term->save();
            $term_ids[] = ['target_id' => $term->id()];
          }
        }
      }
      
      $user->set('field_favorite_authors', $term_ids);
      
      try {
        $user->save();
        
        // Invalidate user cache
        \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
        
        $this->messenger()->addStatus($this->t('Your favorite authors have been updated.'));
      } catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while updating your favorites.'));
        \Drupal::logger('storyfulls_profile')->error('Authors update error: @error', ['@error' => $e->getMessage()]);
      }
    }
    
    return new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
  }

  /**
   * Handle books form submission.
   */
  private function handleBooksSubmit(Request $request, User $user) {
    $books_json = $request->request->get('books_data');
    
    if ($books_json) {
      $book_ids = json_decode($books_json, TRUE);
      $node_refs = [];
      
      foreach ($book_ids as $book_id) {
        if (!empty($book_id) && is_numeric($book_id)) {
          $node_refs[] = ['target_id' => $book_id];
        }
      }
      
      $user->set('field_favorite_books', $node_refs);
      
      try {
        $user->save();
        
        // Invalidate user cache
        \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
        
        $this->messenger()->addStatus($this->t('Your favorite books have been updated.'));
      } catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while updating your favorites.'));
        \Drupal::logger('storyfulls_profile')->error('Books update error: @error', ['@error' => $e->getMessage()]);
      }
    }
    
    return new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
  }

  /**
   * Handle genres form submission.
   */
  private function handleGenresSubmit(Request $request, User $user) {
    $genre_ids = $request->request->all('genres');
    
    if (is_array($genre_ids)) {
      $term_refs = [];
      
      foreach ($genre_ids as $genre_id) {
        if (!empty($genre_id) && is_numeric($genre_id)) {
          $term_refs[] = ['target_id' => $genre_id];
        }
      }
      
      $user->set('field_favorite_genres', $term_refs);
      
      try {
        $user->save();
        
        // Invalidate user cache
        \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
        
        $this->messenger()->addStatus($this->t('Your favorite genres have been updated.'));
      } catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while updating your favorites.'));
        \Drupal::logger('storyfulls_profile')->error('Genres update error: @error', ['@error' => $e->getMessage()]);
      }
    }
    
    return new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
  }

}
