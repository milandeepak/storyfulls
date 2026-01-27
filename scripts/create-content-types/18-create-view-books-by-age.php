<?php

echo "📚 Creating Books by Age Group View\n\n";

$config = \Drupal::configFactory()->getEditable('views.view.books_by_age');

$config->setData([
  'langcode' => 'en',
  'status' => TRUE,
  'dependencies' => [
    'module' => ['node', 'taxonomy', 'user'],
  ],
  'id' => 'books_by_age',
  'label' => 'Books by Age Group',
  'module' => 'views',
  'description' => 'Display books filtered by age group',
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
        'title' => 'Books by Age',
        'fields' => [
          'title' => [
            'id' => 'title',
            'table' => 'node_field_data',
            'field' => 'title',
            'relationship' => 'none',
            'label' => '',
            'settings' => ['link_to_entity' => TRUE],
            'plugin_id' => 'field',
          ],
          'field_featured_image' => [
            'id' => 'field_featured_image',
            'table' => 'node__field_featured_image',
            'field' => 'field_featured_image',
            'relationship' => 'none',
            'label' => '',
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
          'field_age_group_target_id' => [
            'id' => 'field_age_group_target_id',
            'table' => 'node__field_age_group',
            'field' => 'field_age_group_target_id',
            'exposed' => TRUE,
            'expose' => [
              'identifier' => 'field_age_group_target_id',
              'label' => 'Age Group',
              'multiple' => TRUE,
            ],
            'plugin_id' => 'taxonomy_index_tid',
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
          'options' => ['items_per_page' => 8],
        ],
        'style' => [
          'type' => 'default',
          'options' => ['row_class' => 'book-item'],
        ],
      ],
    ],
    'block_1' => [
      'id' => 'block_1',
      'display_title' => 'Block',
      'display_plugin' => 'block',
      'position' => 1,
      'display_options' => [
        'display_description' => 'Books filtered by age group',
        'block_description' => 'Books by Age Group',
      ],
    ],
  ],
]);

$config->save();
echo "✓ Books by Age Group view created\n";
echo "  - Block display available\n";
echo "  - Filtered by age group\n";
echo "  - Shows 8 books\n";
echo "\n✅ View ready!\n";
