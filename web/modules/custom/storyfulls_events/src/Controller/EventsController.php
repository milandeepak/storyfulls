<?php

namespace Drupal\storyfulls_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Returns responses for Storyfulls Events routes.
 */
class EventsController extends ControllerBase {

  /**
   * Builds the events page content.
   */
  public function content() {
    // Load all published events sorted by event date
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'event')
      ->condition('status', 1)
      ->sort('field_event_date', 'ASC') // Sort by upcoming events
      ->range(0, 50); // Limit to 50 for now

    $nids = $query->execute();
    $events = [];

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        $event = [
          'id' => $node->id(),
          'title' => $node->getTitle(),
          'description' => '',
          'date' => '',
          'location' => 'Online', // Default
          'age_group' => 'All ages', // Default
          'image_url' => '',
          'url' => $node->toUrl()->toString(),
        ];

        // Description
        if ($node->hasField('field_short_description') && !$node->get('field_short_description')->isEmpty()) {
          $event['description'] = $node->get('field_short_description')->value;
        }

        // Date (Format: 3 April 2025)
        if ($node->hasField('field_event_date') && !$node->get('field_event_date')->isEmpty()) {
          $date_value = $node->get('field_event_date')->date;
          if ($date_value) {
            $event['date'] = $date_value->format('j F Y');
          }
        }

        // Location
        if ($node->hasField('field_event_location') && !$node->get('field_event_location')->isEmpty()) {
          $event['location'] = $node->get('field_event_location')->value;
        }

        // Age Group
        if ($node->hasField('field_age_group') && !$node->get('field_age_group')->isEmpty()) {
          $age_term = $node->get('field_age_group')->entity;
          if ($age_term) {
            $event['age_group'] = $age_term->getName();
          }
        }

        // Featured Image
        if ($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()) {
          $file = $node->get('field_featured_image')->entity;
          if ($file) {
            $event['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }

        // External Link (if any) overrides node link
        if ($node->hasField('field_url') && !$node->get('field_url')->isEmpty()) {
          $event['url'] = $node->get('field_url')->uri;
        }

        $events[] = $event;
      }
    }

    return [
      '#theme' => 'events_page',
      '#events' => $events,
      '#attached' => [
        'library' => [
          'storyfulls/events-page', // We'll add this to libraries.yml
        ],
      ],
    ];
  }

}
