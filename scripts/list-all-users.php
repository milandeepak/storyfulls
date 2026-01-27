<?php

/**
 * List all users created in the system
 */

echo "All Users in the System:\n";
echo str_repeat("=", 80) . "\n\n";

// Get all users except anonymous (uid 0)
$query = \Drupal::entityQuery('user')
  ->condition('uid', 0, '>')
  ->accessCheck(TRUE)
  ->sort('created', 'DESC');

$uids = $query->execute();

if (empty($uids)) {
  echo "No users found (except anonymous).\n";
} else {
  echo "Found " . count($uids) . " user(s):\n\n";
  
  foreach ($uids as $uid) {
    $user = \Drupal\user\Entity\User::load($uid);
    
    echo "User ID: " . $user->id() . "\n";
    echo "Username: " . $user->getAccountName() . "\n";
    echo "Email: " . $user->getEmail() . "\n";
    echo "Status: " . ($user->isActive() ? 'Active' : 'Blocked') . "\n";
    echo "Created: " . date('Y-m-d H:i:s', $user->getCreatedTime()) . "\n";
    echo "Last Login: " . ($user->getLastLoginTime() ? date('Y-m-d H:i:s', $user->getLastLoginTime()) : 'Never') . "\n";
    
    // Check roles
    $roles = $user->getRoles();
    $roles = array_filter($roles, function($role) {
      return $role !== 'authenticated';
    });
    echo "Roles: " . (empty($roles) ? 'None (basic user)' : implode(', ', $roles)) . "\n";
    
    // Check custom field_user_role
    if ($user->hasField('field_user_role')) {
      $user_role_field = $user->get('field_user_role')->value;
      if ($user_role_field) {
        echo "User Type: " . $user_role_field . "\n";
      }
    }
    
    // Check if user has favorites
    if ($user->hasField('field_favorite_authors')) {
      $authors = $user->get('field_favorite_authors')->referencedEntities();
      if (!empty($authors)) {
        $author_names = array_map(function($term) {
          return $term->getName();
        }, $authors);
        echo "Favorite Authors: " . implode(', ', $author_names) . "\n";
      }
    }
    
    if ($user->hasField('field_favorite_books')) {
      $books = $user->get('field_favorite_books')->referencedEntities();
      if (!empty($books)) {
        $book_titles = array_map(function($node) {
          return $node->getTitle();
        }, $books);
        echo "Favorite Books: " . implode(', ', $book_titles) . "\n";
      }
    }
    
    if ($user->hasField('field_favorite_genres')) {
      $genres = $user->get('field_favorite_genres')->referencedEntities();
      if (!empty($genres)) {
        $genre_names = array_map(function($term) {
          return $term->getName();
        }, $genres);
        echo "Favorite Genres: " . implode(', ', $genre_names) . "\n";
      }
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
  }
}
