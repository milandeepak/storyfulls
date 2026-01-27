<?php

/**
 * Debug CTA paragraphs
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

// Find the homepage
$query = \Drupal::entityQuery('node')
  ->condition('type', 'page')
  ->condition('title', 'Homepage')
  ->accessCheck(FALSE)
  ->range(0, 1);
$nids = $query->execute();

if (empty($nids)) {
  echo "Homepage not found!\n";
  exit;
}

$nid = reset($nids);
$node = Node::load($nid);

echo "Homepage Node ID: $nid\n\n";

// Get all paragraphs
$paragraphs = $node->get('field_content_sections')->referencedEntities();

echo "Total paragraphs: " . count($paragraphs) . "\n\n";

foreach ($paragraphs as $index => $paragraph) {
  $type = $paragraph->bundle();
  echo ($index + 1) . ". Type: $type (ID: " . $paragraph->id() . ")\n";
  
  if ($type === 'cta') {
    // Check CTA fields
    echo "   - Heading: " . ($paragraph->hasField('field_heading') ? $paragraph->get('field_heading')->value : 'FIELD NOT FOUND') . "\n";
    echo "   - Body: " . ($paragraph->hasField('field_body_text') ? substr($paragraph->get('field_body_text')->value, 0, 50) : 'FIELD NOT FOUND') . "\n";
    echo "   - Button Text: " . ($paragraph->hasField('field_button_text') ? $paragraph->get('field_button_text')->value : 'FIELD NOT FOUND') . "\n";
    echo "   - Background Color: " . ($paragraph->hasField('field_background_color') ? $paragraph->get('field_background_color')->value : 'FIELD NOT FOUND') . "\n";
  }
  
  echo "\n";
}

// List all fields on CTA paragraph type
echo "\n=== CTA Paragraph Type Fields ===\n";
$field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', 'cta');
foreach ($field_definitions as $field_name => $field_definition) {
  if (strpos($field_name, 'field_') === 0) {
    echo "- $field_name: " . $field_definition->getType() . "\n";
  }
}
