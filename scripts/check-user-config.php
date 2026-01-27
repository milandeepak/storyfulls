<?php

/**
 * Check user registration configuration
 */

// Check registration setting
$config = \Drupal::config('user.settings');
$register_setting = $config->get('register');

echo "User Registration Settings:\n";
echo "==========================\n\n";
echo "register: $register_setting\n";
echo "  (visitors = open registration)\n";
echo "  (admin_only = only admins can create users)\n";
echo "  (visitors_admin_approval = visitors can register but need approval)\n\n";

// Check if email verification is required
$verify_mail = $config->get('verify_mail');
echo "verify_mail: " . ($verify_mail ? 'true' : 'false') . "\n";
echo "  (if true, users get email to set password)\n\n";

// Check the last created user
$query = \Drupal::entityQuery('user')
  ->accessCheck(TRUE)
  ->sort('created', 'DESC')
  ->range(0, 1);

$uids = $query->execute();

if (!empty($uids)) {
  $uid = reset($uids);
  $user = \Drupal\user\Entity\User::load($uid);
  
  echo "Last Created User:\n";
  echo "==================\n";
  echo "ID: " . $user->id() . "\n";
  echo "Name: " . $user->getAccountName() . "\n";
  echo "Email: " . $user->getEmail() . "\n";
  echo "Status: " . ($user->isActive() ? 'Active' : 'Blocked') . "\n";
  echo "Created: " . date('Y-m-d H:i:s', $user->getCreatedTime()) . "\n";
  echo "Has password: " . (!empty($user->getPassword()) ? 'Yes' : 'No') . "\n";
  
  if ($user->hasField('field_user_role')) {
    $role = $user->get('field_user_role')->value;
    echo "User Role Field: " . ($role ?: 'Not set') . "\n";
  }
}

echo "\n";
