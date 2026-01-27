<?php

$fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'book');

echo "=== CURRENT BOOK FIELDS ===\n\n";
foreach ($fields as $field_name => $field) {
  if (strpos($field_name, 'field_') === 0) {
    $type = $field->getType();
    echo "✓ $field_name ({$field->getLabel()}) - Type: $type\n";
  }
}

echo "\n=== FIELDS NEEDED FOR DESIGN ===\n\n";
$needed_fields = [
  'field_description' => 'Book description/summary',
  'field_buy_link' => 'Amazon/Buy link',
  'field_illustrator' => 'Illustrator (taxonomy)',
  'field_genre' => 'Genre (taxonomy)',
  'field_publisher' => 'Publisher (taxonomy)',
  'field_tags' => 'Tags (taxonomy)',
  'field_isbn' => 'ISBN number',
];

foreach ($needed_fields as $field_name => $description) {
  $exists = array_key_exists($field_name, $fields);
  $status = $exists ? '✓ EXISTS' : '✗ NEEDS TO BE CREATED';
  echo "$status - $field_name: $description\n";
}

echo "\n";
