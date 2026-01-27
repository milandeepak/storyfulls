<?php

/**
 * Disable email verification for user registration
 * This allows users to set password during registration and login immediately
 */

echo "Updating user registration settings...\n\n";

// Get user settings
$config = \Drupal::configFactory()->getEditable('user.settings');

// Current setting
$current = $config->get('verify_mail');
echo "Current verify_mail setting: " . ($current ? 'true' : 'false') . "\n";

// Disable email verification
$config->set('verify_mail', FALSE)->save();

echo "Updated verify_mail setting: false\n\n";

echo "✓ Users can now set passwords during registration and login immediately!\n";
echo "✓ No email verification required\n\n";
