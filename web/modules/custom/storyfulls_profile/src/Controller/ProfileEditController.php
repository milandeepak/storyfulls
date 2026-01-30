<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for profile editing page.
 */
class ProfileEditController extends ControllerBase {

  /**
   * Display edit profile page.
   */
  public function editProfile(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());
    
    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }
    
    // Handle form submission
    if ($request->isMethod('POST')) {
      return $this->handleFormSubmit($request, $user);
    }
    
    // Get user data
    $user_data = [
      'user_id' => $user->id(),
      'first_name' => $user->hasField('field_first_name') && !$user->get('field_first_name')->isEmpty() ? $user->get('field_first_name')->value : '',
      'last_name' => $user->hasField('field_last_name') && !$user->get('field_last_name')->isEmpty() ? $user->get('field_last_name')->value : '',
      'email' => $user->getEmail(),
      'age' => $user->hasField('field_age') && !$user->get('field_age')->isEmpty() ? $user->get('field_age')->value : '',
      'date_of_birth' => $user->hasField('field_date_of_birth') && !$user->get('field_date_of_birth')->isEmpty() ? $user->get('field_date_of_birth')->value : '',
      'gender' => $user->hasField('field_gender') && !$user->get('field_gender')->isEmpty() ? $user->get('field_gender')->value : '',
      'city' => $user->hasField('field_city') && !$user->get('field_city')->isEmpty() ? $user->get('field_city')->value : '',
      'country' => $user->hasField('field_country') && !$user->get('field_country')->isEmpty() ? $user->get('field_country')->value : '',
      'currently_reading' => $user->hasField('field_currently_reading') && !$user->get('field_currently_reading')->isEmpty() ? $user->get('field_currently_reading')->value : '',
      'bio' => $user->hasField('field_bio') && !$user->get('field_bio')->isEmpty() ? $user->get('field_bio')->value : '',
      'about_me' => $user->hasField('field_about_me') && !$user->get('field_about_me')->isEmpty() ? $user->get('field_about_me')->value : '',
      'avatar' => $user->hasField('field_avatar') && !$user->get('field_avatar')->isEmpty() ? $user->get('field_avatar')->value : 'elephantavatar.png',
    ];
    
    return [
      '#theme' => 'storyfulls_edit_profile',
      '#user_data' => $user_data,
      '#attached' => [
        'library' => [
          'storyfulls/edit-profile',
        ],
      ],
    ];
  }
  
  /**
   * Handle form submission.
   */
  private function handleFormSubmit(Request $request, User $user) {
    // Get form data
    $first_name = $request->request->get('first_name');
    $last_name = $request->request->get('last_name');
    $age = $request->request->get('age');
    $date_of_birth = $request->request->get('date_of_birth');
    $gender = $request->request->get('gender');
    $city = $request->request->get('city');
    $country = $request->request->get('country');
    $currently_reading = $request->request->get('currently_reading');
    $bio = $request->request->get('bio');
    $about_me = $request->request->get('about_me');
    $avatar = $request->request->get('avatar');
    
    // Update user fields
    if ($first_name !== NULL && $user->hasField('field_first_name')) {
      $user->set('field_first_name', $first_name);
    }
    if ($last_name !== NULL && $user->hasField('field_last_name')) {
      $user->set('field_last_name', $last_name);
    }
    if ($age !== NULL && $user->hasField('field_age')) {
      $user->set('field_age', $age);
    }
    if ($date_of_birth !== NULL && $user->hasField('field_date_of_birth')) {
      $user->set('field_date_of_birth', $date_of_birth);
    }
    if ($gender !== NULL && $user->hasField('field_gender')) {
      $user->set('field_gender', $gender);
    }
    if ($city !== NULL && $user->hasField('field_city')) {
      $user->set('field_city', $city);
    }
    if ($country !== NULL && $user->hasField('field_country')) {
      $user->set('field_country', $country);
    }
    if ($currently_reading !== NULL && $user->hasField('field_currently_reading')) {
      $user->set('field_currently_reading', $currently_reading);
    }
    if ($bio !== NULL && $user->hasField('field_bio')) {
      $user->set('field_bio', $bio);
    }
    if ($about_me !== NULL && $user->hasField('field_about_me')) {
      $user->set('field_about_me', $about_me);
    }
    
    // Handle avatar selection
    if ($avatar !== NULL && $user->hasField('field_avatar')) {
      // Validate avatar is one of the allowed options
      $allowed_avatars = ['elephantavatar.png', 'tigeravatar.png', 'rhinoavatar.png'];
      if (in_array($avatar, $allowed_avatars)) {
        $user->set('field_avatar', $avatar);
      }
    }
    
    try {
      $user->save();
      // Invalidate user cache so the profile page shows updated data after redirect.
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
      $this->messenger()->addStatus($this->t('Your profile has been updated successfully.'));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('An error occurred while updating your profile.'));
      \Drupal::logger('storyfulls_profile')->error('Profile update error: @error', ['@error' => $e->getMessage()]);
    }
    
    return new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
  }

  /**
   * Display edit This Week's Pick page.
   */
  public function editWeeksPick(Request $request) {
    $current_user = $this->currentUser();
    $user = User::load($current_user->id());

    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Handle form submission.
    if ($request->isMethod('POST')) {
      return $this->handleWeeksPickSubmit($request, $user);
    }

    $weeks_pick_book = $user->hasField('field_weeks_pick_book') && !$user->get('field_weeks_pick_book')->isEmpty()
      ? $user->get('field_weeks_pick_book')->value : '';
    $weeks_pick_author = $user->hasField('field_weeks_pick_author') && !$user->get('field_weeks_pick_author')->isEmpty()
      ? $user->get('field_weeks_pick_author')->value : '';

    return [
      '#theme' => 'storyfulls_edit_weeks_pick',
      '#user_id' => $user->id(),
      '#weeks_pick_book' => $weeks_pick_book,
      '#weeks_pick_author' => $weeks_pick_author,
      '#attached' => [
        'library' => [
          'storyfulls/edit-favorites',
        ],
      ],
    ];
  }

  /**
   * Handle This Week's Pick form submission.
   */
  private function handleWeeksPickSubmit(Request $request, User $user) {
    $book = $request->request->get('weeks_pick_book');
    $author = $request->request->get('weeks_pick_author');

    if ($book !== NULL && $user->hasField('field_weeks_pick_book')) {
      $user->set('field_weeks_pick_book', $book);
    }
    if ($author !== NULL && $user->hasField('field_weeks_pick_author')) {
      $user->set('field_weeks_pick_author', $author);
    }

    try {
      $user->save();
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['user:' . $user->id()]);
      $this->messenger()->addStatus($this->t("This Week's Pick has been updated successfully."));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t("An error occurred while updating This Week's Pick."));
      \Drupal::logger('storyfulls_profile')->error('Weeks pick update error: @error', ['@error' => $e->getMessage()]);
    }

    return new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
  }

}
