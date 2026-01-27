<?php

echo "⭐ Creating Featured Books View\n\n";

$config = \Drupal::configFactory()->getEditable('views.view.featured_books');

$config->setData([
  'langcode' => 'en',
  'status' => TRUE,
  'dependencies' => ['module' => ['node', 'user']],
  'id' => 'featured_books',
  'label' => 'Featured Books',
  'module' => 'views',
  'description' => 'Display featured or highly rated books',
  'tag' => '',
  'base_table' => 'node_field_data',
  'base_field' => 'nid',
  'display' => [
    'default' => [
      'id' => 'default',
      'display_title' => 'Default',
      'display_plugin' => 'default',
      'position' => 0,
      'display_options' => [
        'title' => 'Featured Books',
        'fields' => [
          'title' => [
            'id' => 'title',
            'table' => 'node_field_data',
            'field' => 'title',
            'settings' => ['link_to_entity' => TRUE],
            'plugin_id' => 'field',
          ],
          'field_featured_image' => [
            'id' => 'field_featured_image',
            'table' => 'node__field_featured_image',
            'field' => 'field_featured_image',
            'settings' => [
              'image_style' => 'medium',
              'image_link' => 'content',
            ],
            'plugin_id' => 'field',
          ],
        ],
        'filters' => [
          'status' => [
            'id' => 'status',
            'table' => 'node_field_data',
            'field' => 'status',
            'value' => '1',
            'plugin_id' => 'boolean',
          ],
          'type' => [
            'id' => 'type',
            'table' => 'node_field_data',
            'field' => 'type',
            'value' => ['book' => 'book'],
            'plugin_id' => 'bundle',
          ],
        ],
        'sorts' => [
          'created' => [
            'id' => 'created',
            'table' => 'node_field_data',
            'field' => 'created',
            'order' => 'DESC',
            'plugin_id' => 'date',
          ],
        ],
        'pager' => [
          'type' => 'some',
          'options' => ['items_per_page' => 6],
        ],
      ],
    ],
    'block_1' => [
      'id' => 'block_1',
      'display_title' => 'Block',
      'display_plugin' => 'block',
      'position' => 1,
      'display_options' => [
        'block_description' => 'Featured Books',
      ],
    ],
  ],
]);

$config->save();
echo "✓ Featured Books view created\n";
echo "  - Shows 6 most recent books\n";
echo "\n✅ View ready!\n";
