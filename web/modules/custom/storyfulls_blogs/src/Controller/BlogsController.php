<?php

namespace Drupal\storyfulls_blogs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Controller for the Blogs page.
 */
class BlogsController extends ControllerBase {

  /**
   * Returns content for the Blogs page.
   */
  public function content() {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'blog')
      ->condition('status', 1)
      ->sort('created', 'DESC');
    
    $nids = $query->execute();
    $blogs = [];
    
    // Default images
    $default_image = '/themes/custom/storyfulls/images/blogeventcard.png';
    
    if (!empty($nids)) {
      $nodes = $storage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $img_url = $default_image;
        if ($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()) {
          $file = $node->get('field_featured_image')->entity;
          if ($file instanceof File) {
            $img_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }

        $blogs[] = [
          'title' => $node->getTitle(),
          'url' => $node->toUrl()->toString(),
          'image_url' => $img_url,
          'summary' => text_summary($node->body->value, $node->body->format, 100),
          'date' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'd M, Y'),
        ];
      }
    }

    return [
      '#theme' => 'blogs_page',
      '#blogs' => $blogs,
      '#attached' => [
        'library' => [
          'storyfulls/blogs-page',
        ],
      ],
    ];
  }

}
