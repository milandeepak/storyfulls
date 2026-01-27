<?php

echo "📝 Creating Recent Blogs View\n\n";

$config = \Drupal::configFactory()->getEditable('views.view.recent_blogs');

$config->setData([
  'langcode' => 'en',
  'status' => TRUE,
  'dependencies' => ['module' => ['node', 'user']],
  'id' => 'recent_blogs',
  'label' => 'Recent Blogs',
  'module' => 'views',
  'description' => 'Display recent blog posts',
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
        'title' => 'Recent Blogs',
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
          'field_short_description' => [
            'id' => 'field_short_description',
            'table' => 'node__field_short_description',
            'field' => 'field_short_description',
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
            'value' => ['blog' => 'blog'],
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
        'block_description' => 'Recent Blogs',
      ],
    ],
  ],
]);

$config->save();
echo "✓ Recent Blogs view created\n";
echo "  - Shows 6 most recent blogs\n";
echo "\n✅ View ready!\n";
