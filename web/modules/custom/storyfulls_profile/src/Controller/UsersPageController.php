<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller for the Users Listing Page.
 */
class UsersPageController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a UsersPageController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Displays the users listing page.
   */
  public function listUsers() {
    // Query active users.
    $query = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('status', 1)
      ->condition('uid', 1, '>') // Exclude user 1 (super admin)
      ->accessCheck(TRUE);
    
    // Execute query to get uids.
    $uids = $query->execute();
    
    // Load user entities.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
    
    // Filter out administrators role if needed (assuming 'administrator' is the role id).
    // The requirement says "Don't show the admin on the page".
    $users = array_filter($users, function($user) {
      return !$user->hasRole('administrator');
    });

    // Build the user cards data.
    $user_cards = [];
    foreach ($users as $user) {
      // Get User Avatar from field_avatar or assign cyclically.
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
      
      // Get Name (First + Last or Username).
      $first_name = $user->hasField('field_first_name') && !$user->field_first_name->isEmpty() ? $user->field_first_name->value : '';
      $last_name = $user->hasField('field_last_name') && !$user->field_last_name->isEmpty() ? $user->field_last_name->value : '';
      $name = trim($first_name . ' ' . $last_name);
      if (empty($name)) {
        $name = $user->getDisplayName();
      }

      // Get Location (Country/City).
      $country = $user->hasField('field_country') && !$user->field_country->isEmpty() ? $user->field_country->value : '';
      
      // Get Age (from DOB).
      $age = 'Age not available';
      if ($user->hasField('field_date_of_birth') && !$user->field_date_of_birth->isEmpty()) {
        $dob = $user->field_date_of_birth->value;
        if ($dob) {
           $birthDate = new \DateTime($dob);
           $today = new \DateTime('today');
           $age = $birthDate->diff($today)->y;
        }
      }

      // Get Review Count.
      // Assuming reviews are nodes of type 'review' created by this user.
      $review_count = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'book_review') // Assuming 'book_review' is the machine name
        ->condition('uid', $user->id())
        ->condition('status', 1)
        ->accessCheck(TRUE)
        ->count()
        ->execute();

      $user_cards[] = [
        'uid' => $user->id(),
        'name' => $name,
        'picture' => $user_picture,
        'location' => $country, // Showing only country as per request "location" usually implies country or city/country
        'age' => $age,
        'review_count' => $review_count,
        'url' => $user->toUrl()->toString(),
      ];
    }

    // Sort users by review count descending (Most Popular Reviewers).
    usort($user_cards, function($a, $b) {
      return $b['review_count'] <=> $a['review_count'];
    });

    return [
      '#theme' => 'storyfulls_users_page',
      '#users' => $user_cards,
      '#attached' => [
        'library' => [
          'storyfulls_profile/users_page',
        ],
      ],
    ];
  }

}
