<?php

namespace Drupal\storyfulls_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the admin dashboard.
 */
class AdminDashboardController extends ControllerBase {

  /**
   * Main dashboard page.
   */
  public function dashboard() {
    return [
      '#theme' => 'admin_dashboard',
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage books page.
   */
  public function manageBooks(Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'book')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    // Handle filtering
    $name = $request->query->get('name');
    $status = $request->query->get('status');

    if (!empty($name)) {
      $query->condition('title', '%' . $name . '%', 'LIKE');
    }

    if (!empty($status)) {
      $query->condition('status', $status == 'yes' ? 1 : 0);
    }

    $nids = $query->execute();
    $books = $storage->loadMultiple($nids);

    $book_list = [];
    $index = 1;
    foreach ($books as $book) {
      $book_list[] = [
        'sl_no' => $index++,
        'name' => $book->getTitle(),
        'nid' => $book->id(),
      ];
    }

    return [
      '#theme' => 'manage_books',
      '#books' => $book_list,
      '#current_name' => $name,
      '#current_status' => $status,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage blogs page.
   */
  public function manageBlogs(Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'blog')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    // Handle filtering
    $name = $request->query->get('name');
    $status = $request->query->get('status');

    if (!empty($name)) {
      $query->condition('title', '%' . $name . '%', 'LIKE');
    }

    if (!empty($status)) {
      $query->condition('status', $status == 'yes' ? 1 : 0);
    }

    $nids = $query->execute();
    $blogs = $storage->loadMultiple($nids);

    $blog_list = [];
    $index = 1;
    foreach ($blogs as $blog) {
      $blog_list[] = [
        'sl_no' => $index++,
        'name' => $blog->getTitle(),
        'nid' => $blog->id(),
      ];
    }

    return [
      '#theme' => 'manage_blogs',
      '#blogs' => $blog_list,
      '#current_name' => $name,
      '#current_status' => $status,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage age group page.
   */
  public function manageAgeGroup(Request $request) {
    return $this->manageTaxonomy('age_group', 'Age Groups', $request);
  }

  /**
   * Manage genre page.
   */
  public function manageGenre(Request $request) {
    return $this->manageTaxonomy('genre', 'Genres', $request);
  }

  /**
   * Manage tags page.
   */
  public function manageTags(Request $request) {
    return $this->manageTaxonomy('tags', 'Tags', $request);
  }

  /**
   * Helper function to manage taxonomy terms.
   */
  private function manageTaxonomy($vocabulary, $label, Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $query = $storage->getQuery()
      ->condition('vid', $vocabulary)
      ->accessCheck(TRUE)
      ->sort('name', 'ASC');

    // Handle filtering
    $name = $request->query->get('name');

    if (!empty($name)) {
      $query->condition('name', '%' . $name . '%', 'LIKE');
    }

    $tids = $query->execute();
    $terms = $storage->loadMultiple($tids);

    $term_list = [];
    $index = 1;
    foreach ($terms as $term) {
      $term_list[] = [
        'sl_no' => $index++,
        'name' => $term->getName(),
        'tid' => $term->id(),
      ];
    }

    // Create singular label by removing 's' from end if it exists
    $singular_label = $label;
    if (substr($label, -1) === 's') {
      $singular_label = substr($label, 0, -1);
    }

    return [
      '#theme' => 'manage_taxonomy',
      '#terms' => $term_list,
      '#vocabulary' => $vocabulary,
      '#label' => $label,
      '#singular_label' => $singular_label,
      '#current_name' => $name,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage write ups page.
   */
  public function manageWriteUps(Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'write_up')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    $name = $request->query->get('name');
    $status = $request->query->get('status');

    if (!empty($name)) {
      $query->condition('title', '%' . $name . '%', 'LIKE');
    }

    if (!empty($status)) {
      $query->condition('status', $status == 'yes' ? 1 : 0);
    }

    $nids = $query->execute();
    $writeups = $storage->loadMultiple($nids);

    $writeup_list = [];
    $index = 1;
    foreach ($writeups as $writeup) {
      $writeup_list[] = [
        'sl_no' => $index++,
        'name' => $writeup->getTitle(),
        'nid' => $writeup->id(),
      ];
    }

    return [
      '#theme' => 'manage_write_ups',
      '#writeups' => $writeup_list,
      '#current_name' => $name,
      '#current_status' => $status,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage events page.
   */
  public function manageEvents(Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'event')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    $name = $request->query->get('name');
    $status = $request->query->get('status');

    if (!empty($name)) {
      $query->condition('title', '%' . $name . '%', 'LIKE');
    }

    if (!empty($status)) {
      $query->condition('status', $status == 'yes' ? 1 : 0);
    }

    $nids = $query->execute();
    $events = $storage->loadMultiple($nids);

    $event_list = [];
    $index = 1;
    foreach ($events as $event) {
      $event_list[] = [
        'sl_no' => $index++,
        'name' => $event->getTitle(),
        'nid' => $event->id(),
      ];
    }

    return [
      '#theme' => 'manage_events',
      '#events' => $event_list,
      '#current_name' => $name,
      '#current_status' => $status,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage announcement page.
   */
  public function manageAnnouncement(Request $request) {
    // This would typically manage a single announcement config or content
    return [
      '#theme' => 'manage_announcement',
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage users page.
   */
  public function manageUsers(Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $storage->getQuery()
      ->condition('uid', 0, '>')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    $name = $request->query->get('name');

    if (!empty($name)) {
      $or = $query->orConditionGroup()
        ->condition('name', '%' . $name . '%', 'LIKE')
        ->condition('mail', '%' . $name . '%', 'LIKE');
      $query->condition($or);
    }

    $uids = $query->execute();
    $users = $storage->loadMultiple($uids);

    $user_list = [];
    $index = 1;
    foreach ($users as $user) {
      $user_list[] = [
        'sl_no' => $index++,
        'name' => $user->getDisplayName(),
        'email' => $user->getEmail(),
        'uid' => $user->id(),
      ];
    }

    return [
      '#theme' => 'manage_users',
      '#users' => $user_list,
      '#current_name' => $name,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage reviews page.
   */
  public function manageReviews(Request $request) {
    // Assuming reviews are stored as comments on book nodes
    $storage = \Drupal::entityTypeManager()->getStorage('comment');
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    $cids = $query->execute();
    $comments = $storage->loadMultiple($cids);

    $review_list = [];
    $index = 1;
    foreach ($comments as $comment) {
      $review_list[] = [
        'sl_no' => $index++,
        'subject' => $comment->getSubject(),
        'author' => $comment->getOwner()->getDisplayName(),
        'cid' => $comment->id(),
      ];
    }

    return [
      '#theme' => 'manage_reviews',
      '#reviews' => $review_list,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Book of the Season page.
   */
  public function bookOfSeason(Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      $book_id = $request->request->get('book');
      if ($book_id) {
        // Load homepage node
        $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
        if ($homepage && $homepage->hasField('field_content_sections')) {
          $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
          foreach ($paragraphs as $paragraph) {
            if ($paragraph->bundle() == 'book_of_season') {
              $paragraph->set('field_featured_book', $book_id);
              $paragraph->save();
              \Drupal::messenger()->addMessage('Book of the Season updated successfully.');
              break;
            }
          }
        }
      }
    }

    // Get current book of the season
    $current_book_id = NULL;
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        if ($paragraph->bundle() == 'book_of_season') {
          if ($paragraph->hasField('field_featured_book') && !$paragraph->get('field_featured_book')->isEmpty()) {
            $current_book_id = $paragraph->get('field_featured_book')->target_id;
          }
          break;
        }
      }
    }

    // Load all books for dropdown
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'book')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');

    $nids = $query->execute();
    $books = $storage->loadMultiple($nids);

    $book_list = [];
    foreach ($books as $book) {
      $book_list[] = [
        'name' => $book->getTitle(),
        'nid' => $book->id(),
        'selected' => ($book->id() == $current_book_id),
      ];
    }

    return [
      '#theme' => 'book_of_season',
      '#books' => $book_list,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Delete book.
   */
  public function deleteBook($nid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    if ($node && $node->bundle() == 'book') {
      $node->delete();
      \Drupal::messenger()->addMessage('Book deleted successfully.');
    }
    return new RedirectResponse('/admin/dashboard/manage-books');
  }

  /**
   * Delete blog.
   */
  public function deleteBlog($nid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    if ($node && $node->bundle() == 'blog') {
      $node->delete();
      \Drupal::messenger()->addMessage('Blog deleted successfully.');
    }
    return new RedirectResponse('/admin/dashboard/manage-blogs');
  }

  /**
   * Delete taxonomy term.
   */
  public function deleteTerm($tid, Request $request) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    if ($term) {
      $vocab = $term->bundle();
      $term->delete();
      \Drupal::messenger()->addMessage('Term deleted successfully.');
      
      // Redirect based on vocabulary
      $redirect_map = [
        'age_group' => '/admin/dashboard/manage-age-group',
        'genre' => '/admin/dashboard/manage-genre',
        'tags' => '/admin/dashboard/manage-tags',
      ];
      
      $redirect = $redirect_map[$vocab] ?? '/admin/dashboard';
      return new RedirectResponse($redirect);
    }
    return new RedirectResponse('/admin/dashboard');
  }

  /**
   * Delete user.
   */
  public function deleteUser($uid) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user && $uid != 1) { // Don't allow deleting user 1
      $user->delete();
      \Drupal::messenger()->addMessage('User deleted successfully.');
    }
    return new RedirectResponse('/admin/dashboard/manage-users');
  }

}
