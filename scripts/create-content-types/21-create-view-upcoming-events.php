<?php

echo "📅 Creating Upcoming Events View\n\n";

$config = \Drupal::configFactory()->getEditable('views.view.upcoming_events');

$config->setData([
  'langcode' => 'en',
  'status' => TRUE,
  'dependencies' => ['module' => ['node', 'user', 'datetime']],
  'id' => 'upcoming_events',
  'label' => 'Upcoming Events',
  'module' => 'views',
  'description' => 'Display upcoming events',
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
        'title' => 'Upcoming Events',
        'fields' => [
          'title' => [
            'id' => 'title',
            'table' => 'node_field_data',
            'field' => 'title',
            'settings' => ['link_to_entity' => TRUE],
            'plugin_id' => 'field',
          ],
          'field_event_date' => [
            'id' => 'field_event_date',
            'table' => 'node__field_event_date',
            'field' => 'field_event_date',
            'settings' => ['format_type' => 'medium'],
            'plugin_id' => 'field',
          ],
          'field_event_location' => [
            'id' => 'field_event_location',
            'table' => 'node__field_event_location',
            'field' => 'field_event_location',
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
            'value' => ['event' => 'event'],
            'plugin_id' => 'bundle',
          ],
        ],
        'sorts' => [
          'field_event_date_value' => [
            'id' => 'field_event_date_value',
            'table' => 'node__field_event_date',
            'field' => 'field_event_date_value',
            'order' => 'ASC',
            'plugin_id' => 'datetime',
          ],
        ],
        'pager' => [
          'type' => 'some',
          'options' => ['items_per_page' => 5],
        ],
      ],
    ],
    'block_1' => [
      'id' => 'block_1',
      'display_title' => 'Block',
      'display_plugin' => 'block',
      'position' => 1,
      'display_options' => [
        'block_description' => 'Upcoming Events',
      ],
    ],
  ],
]);

$config->save();
echo "✓ Upcoming Events view created\n";
echo "  - Shows 5 upcoming events\n";
echo "  - Sorted by event date\n";
echo "\n✅ View ready!\n";
