<?php

echo "✍️ Creating Recent Write-ups View\n\n";

$config = \Drupal::configFactory()->getEditable('views.view.recent_writeups');

$config->setData([
  'langcode' => 'en',
  'status' => TRUE,
  'dependencies' => ['module' => ['node', 'user']],
  'id' => 'recent_writeups',
  'label' => 'Recent Write-ups',
  'module' => 'views',
  'description' => 'Display recent stories and poems from young writers',
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
        'title' => 'Young Writers',
        'fields' => [
          'title' => [
            'id' => 'title',
            'table' => 'node_field_data',
            'field' => 'title',
            'settings' => ['link_to_entity' => TRUE],
            'plugin_id' => 'field',
          ],
          'field_story_type' => [
            'id' => 'field_story_type',
            'table' => 'node__field_story_type',
            'field' => 'field_story_type',
            'plugin_id' => 'field',
          ],
          'uid' => [
            'id' => 'uid',
            'table' => 'node_field_data',
            'field' => 'uid',
            'label' => 'Author',
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
            'value' => ['write_up' => 'write_up'],
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
        'block_description' => 'Recent Write-ups',
      ],
    ],
  ],
]);

$config->save();
echo "✓ Recent Write-ups view created\n";
echo "  - Shows 6 most recent submissions\n";
echo "\n✅ View ready!\n";
