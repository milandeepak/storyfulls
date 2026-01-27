<?php

namespace Drupal\storyfulls_auth\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for role selection page.
 */
class RoleSelectionController extends ControllerBase {

  /**
   * Display role selection page.
   */
  public function selectRole() {
    return [
      '#theme' => 'role_selection_page',
      '#attached' => [
        'library' => [
          'storyfulls/auth',
        ],
      ],
    ];
  }

}
