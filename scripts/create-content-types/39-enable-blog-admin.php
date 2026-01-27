<?php

echo "🚀 Enabling Blog Admin Access...\n\n";

// Enable admin menu for blogs - show in Content menu
$config = \Drupal::configFactory()->getEditable('node.type.blog');
$config->set('menu_ui.available_menus', ['main']);
$config->set('menu_ui.parent', 'main:');
$config->save();

echo "✅ Blog content type configured for admin access\n";

// Grant permissions to administrators
$role = \Drupal\user\Entity\Role::load('administrator');
if ($role) {
  $permissions = [
    'create blog content',
    'edit own blog content',
    'edit any blog content',
    'delete own blog content',
    'delete any blog content',
  ];
  
  foreach ($permissions as $permission) {
    $role->grantPermission($permission);
  }
  $role->save();
  echo "✅ Granted blog permissions to administrators\n";
}

// Also grant to authenticated users (content creators)
$auth_role = \Drupal\user\Entity\Role::load('authenticated');
if ($auth_role) {
  $auth_permissions = [
    'create blog content',
    'edit own blog content',
    'delete own blog content',
  ];
  
  foreach ($auth_permissions as $permission) {
    $auth_role->grantPermission($permission);
  }
  $auth_role->save();
  echo "✅ Granted blog permissions to authenticated users\n";
}

echo "\n✅ Done! Admins can now create/edit/delete blogs from /admin/content\n";
echo "   Or directly add new blog: /node/add/blog\n";
