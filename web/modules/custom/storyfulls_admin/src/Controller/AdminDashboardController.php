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

  /**
   * Fun Facts page.
   */
  public function funFacts(Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      $title = $request->request->get('title');
      $content = $request->request->get('content');
      $image_fid = $request->request->get('image');
      
      // Load homepage node
      $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
      if ($homepage && $homepage->hasField('field_content_sections')) {
        $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
        foreach ($paragraphs as $paragraph) {
          if ($paragraph->bundle() == 'fun_facts') {
            $paragraph->set('field_title', $title);
            $paragraph->set('field_content', $content);
            if ($image_fid) {
              $paragraph->set('field_featured_image', $image_fid);
            }
            $paragraph->save();
            \Drupal::messenger()->addMessage('Fun Facts updated successfully.');
            break;
          }
        }
      }
    }

    // Get current fun facts data
    $current_title = '';
    $current_content = '';
    $current_image = NULL;
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        if ($paragraph->bundle() == 'fun_facts') {
          if ($paragraph->hasField('field_title') && !$paragraph->get('field_title')->isEmpty()) {
            $current_title = $paragraph->get('field_title')->value;
          }
          if ($paragraph->hasField('field_content') && !$paragraph->get('field_content')->isEmpty()) {
            $current_content = $paragraph->get('field_content')->value;
          }
          if ($paragraph->hasField('field_featured_image') && !$paragraph->get('field_featured_image')->isEmpty()) {
            $file = $paragraph->get('field_featured_image')->entity;
            if ($file) {
              $current_image = [
                'fid' => $file->id(),
                'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri()),
              ];
            }
          }
          break;
        }
      }
    }

    return [
      '#theme' => 'fun_facts',
      '#title' => $current_title,
      '#content' => $current_content,
      '#image' => $current_image,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Featured Users of the Month page.
   */
  public function featuredUsersMonth(Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      $author_uid = $request->request->get('author');
      $reviewer_uid = $request->request->get('reviewer');
      
      // Load homepage node
      $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
      if ($homepage && $homepage->hasField('field_content_sections')) {
        $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
        foreach ($paragraphs as $paragraph) {
          if ($paragraph->bundle() == 'featured_users_month') {
            if ($author_uid) {
              $paragraph->set('field_featured_author', $author_uid);
            }
            if ($reviewer_uid) {
              $paragraph->set('field_featured_reviewer', $reviewer_uid);
            }
            $paragraph->save();
            \Drupal::messenger()->addMessage('Featured users updated successfully.');
            break;
          }
        }
      }
    }

    // Get current featured users
    $current_author_uid = NULL;
    $current_reviewer_uid = NULL;
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        if ($paragraph->bundle() == 'featured_users_month') {
          if ($paragraph->hasField('field_featured_author') && !$paragraph->get('field_featured_author')->isEmpty()) {
            $current_author_uid = $paragraph->get('field_featured_author')->target_id;
          }
          if ($paragraph->hasField('field_featured_reviewer') && !$paragraph->get('field_featured_reviewer')->isEmpty()) {
            $current_reviewer_uid = $paragraph->get('field_featured_reviewer')->target_id;
          }
          break;
        }
      }
    }

    // Load all active users (excluding admin user 1)
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $user_storage->getQuery()
      ->condition('uid', 1, '!=')
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->sort('name', 'ASC');

    $uids = $query->execute();
    $users = $user_storage->loadMultiple($uids);

    $user_list = [];
    foreach ($users as $user) {
      $user_list[] = [
        'name' => $user->getDisplayName(),
        'uid' => $user->id(),
      ];
    }

    return [
      '#theme' => 'featured_users_month',
      '#users' => $user_list,
      '#current_author_uid' => $current_author_uid,
      '#current_reviewer_uid' => $current_reviewer_uid,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

  /**
   * Manage Homepage sections.
   */
  public function manageHomepage(Request $request) {
    // Handle section deletion
    if ($request->query->get('delete')) {
      $paragraph_id = $request->query->get('delete');
      $this->deleteHomepageSection($paragraph_id);
      return new RedirectResponse('/admin/dashboard/manage-homepage');
    }

    // Handle reordering
    if ($request->isMethod('POST') && $request->request->get('action') === 'reorder') {
      $order = $request->request->get('section_order');
      if ($order) {
        $this->reorderHomepageSections($order);
        \Drupal::messenger()->addMessage('Homepage sections reordered successfully.');
      }
      return new RedirectResponse('/admin/dashboard/manage-homepage');
    }

    // Load homepage node
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    $sections = [];

    if ($homepage && $homepage->hasField('field_content_sections')) {
      $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
      
      foreach ($paragraphs as $index => $paragraph) {
        $section_info = [
          'id' => $paragraph->id(),
          'type' => $paragraph->bundle(),
          'type_label' => $this->getParagraphTypeLabel($paragraph->bundle()),
          'position' => $index + 1,
          'edit_url' => $this->getSectionEditUrl($paragraph),
          'can_delete' => TRUE, // All sections can be deleted
        ];
        
        $sections[] = $section_info;
      }
    }

    return [
      '#theme' => 'manage_homepage',
      '#sections' => $sections,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
          'core/drupal.ajax',
        ],
      ],
    ];
  }

  /**
   * Get paragraph type label.
   */
  private function getParagraphTypeLabel($bundle) {
    $labels = [
      'books_by_age_section' => 'Books by Age Section',
      'community_picks' => 'Community Picks',
      'interested_section' => 'Interested Section (Young Readers/Writers/Events)',
      'fact_section' => 'Did You Know? Facts',
      'books_by_genres_section' => 'Books by Genres Section',
      'book_of_season' => 'Book of the Season',
      'fun_facts' => 'Fun Facts',
      'featured_users_month' => 'Featured Users of the Month',
    ];
    
    return $labels[$bundle] ?? ucwords(str_replace('_', ' ', $bundle));
  }

  /**
   * Get edit URL for a section.
   */
  private function getSectionEditUrl($paragraph) {
    $bundle = $paragraph->bundle();
    
    // Special admin pages for specific sections
    $admin_pages = [
      'book_of_season' => '/admin/dashboard/book-of-season',
      'fun_facts' => '/admin/dashboard/fun-facts',
      'featured_users_month' => '/admin/dashboard/featured-users-month',
      'fact_section' => '/admin/dashboard/manage-facts',
    ];
    
    if (isset($admin_pages[$bundle])) {
      return $admin_pages[$bundle];
    }
    
    // Default to node edit page
    return '/node/16/edit';
  }

  /**
   * Delete a homepage section.
   */
  private function deleteHomepageSection($paragraph_id) {
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $current_sections = $homepage->get('field_content_sections')->getValue();
      $new_sections = [];
      
      foreach ($current_sections as $section) {
        if ($section['target_id'] != $paragraph_id) {
          $new_sections[] = $section;
        }
      }
      
      $homepage->set('field_content_sections', $new_sections);
      $homepage->save();
      
      // Delete the paragraph entity
      $paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->load($paragraph_id);
      if ($paragraph) {
        $paragraph->delete();
      }
      
      \Drupal::messenger()->addMessage('Section deleted successfully.');
    }
  }

  /**
   * Reorder homepage sections.
   */
  private function reorderHomepageSections($order) {
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $current_sections = $homepage->get('field_content_sections')->getValue();
      $paragraph_map = [];
      
      // Create a map of paragraph IDs to their data
      foreach ($current_sections as $section) {
        $paragraph_map[$section['target_id']] = $section;
      }
      
      // Reorder based on the new order
      $new_sections = [];
      $order_array = explode(',', $order);
      
      foreach ($order_array as $paragraph_id) {
        if (isset($paragraph_map[$paragraph_id])) {
          $new_sections[] = $paragraph_map[$paragraph_id];
        }
      }
      
      $homepage->set('field_content_sections', $new_sections);
      $homepage->save();
    }
  }

  /**
   * Manage Did You Know Facts page.
   */
  public function manageFacts(Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      $question = $request->request->get('question');
      $options = $request->request->get('options');
      $correct_answer = $request->request->get('correct_answer');
      $image_fid = $request->request->get('image');
      
      // Load homepage node
      $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
      if ($homepage && $homepage->hasField('field_content_sections')) {
        $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
        foreach ($paragraphs as $paragraph) {
          if ($paragraph->bundle() == 'fact_section') {
            // Update question
            if ($question) {
              $paragraph->set('field_fact_question', $question);
            }
            
            // Update options (remove empty options and format properly)
            if ($options && is_array($options)) {
              $options_values = array_filter($options, function($value) {
                return !empty(trim($value));
              });
              
              // Format options for Drupal field
              $formatted_options = [];
              foreach ($options_values as $option_value) {
                $formatted_options[] = ['value' => $option_value];
              }
              
              $paragraph->set('field_fact_options', $formatted_options);
            }
            
            // Update correct answer index
            if ($correct_answer !== null && $correct_answer !== '') {
              $paragraph->set('field_correct_answer_index', $correct_answer);
            }
            
            // Update image if provided
            if ($image_fid) {
              $paragraph->set('field_fact_image', $image_fid);
            }
            
            $paragraph->save();
            \Drupal::messenger()->addMessage('Did You Know? fact updated successfully.');
            
            // Redirect to prevent form resubmission
            return new RedirectResponse('/admin/dashboard/manage-facts');
          }
        }
      }
    }

    // Get current fact data
    $current_question = '';
    $current_options = ['', '', '', ''];
    $current_correct_answer = 0;
    $current_image = NULL;
    
    $homepage = \Drupal::entityTypeManager()->getStorage('node')->load(16);
    if ($homepage && $homepage->hasField('field_content_sections')) {
      $paragraphs = $homepage->get('field_content_sections')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        if ($paragraph->bundle() == 'fact_section') {
          // Get question
          if ($paragraph->hasField('field_fact_question') && !$paragraph->get('field_fact_question')->isEmpty()) {
            $current_question = $paragraph->get('field_fact_question')->value;
          }
          
          // Get options
          if ($paragraph->hasField('field_fact_options') && !$paragraph->get('field_fact_options')->isEmpty()) {
            $options_items = $paragraph->get('field_fact_options')->getValue();
            $current_options = [];
            foreach ($options_items as $option) {
              $current_options[] = $option['value'];
            }
            // Ensure we have at least 4 options
            while (count($current_options) < 4) {
              $current_options[] = '';
            }
          }
          
          // Get correct answer index
          if ($paragraph->hasField('field_correct_answer_index') && !$paragraph->get('field_correct_answer_index')->isEmpty()) {
            $current_correct_answer = $paragraph->get('field_correct_answer_index')->value;
          }
          
          // Get image
          if ($paragraph->hasField('field_fact_image') && !$paragraph->get('field_fact_image')->isEmpty()) {
            $file = $paragraph->get('field_fact_image')->entity;
            if ($file) {
              $current_image = [
                'fid' => $file->id(),
                'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri()),
              ];
            }
          }
          
          break;
        }
      }
    }

    return [
      '#theme' => 'manage_facts',
      '#question' => $current_question,
      '#options' => $current_options,
      '#correct_answer' => $current_correct_answer,
      '#image' => $current_image,
      '#attached' => [
        'library' => [
          'storyfulls/admin-dashboard',
        ],
      ],
    ];
  }

}
