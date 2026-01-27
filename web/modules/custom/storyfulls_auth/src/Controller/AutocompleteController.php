<?php

namespace Drupal\storyfulls_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns autocomplete responses for authors and books.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Returns autocomplete suggestions for authors.
   */
  public function authorsAutocomplete(Request $request) {
    $matches = [];
    $string = $request->query->get('q');

    if ($string) {
      // Query taxonomy terms for authors (vocabulary is 'author' not 'authors')
      $query = \Drupal::entityQuery('taxonomy_term')
        ->condition('vid', 'author')
        ->condition('name', '%' . $string . '%', 'LIKE')
        ->range(0, 10)
        ->sort('name', 'ASC')
        ->accessCheck(TRUE);
      
      $tids = $query->execute();
      
      if (!empty($tids)) {
        $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
        
        foreach ($terms as $term) {
          $term_name = $term->getName();
          
          // Count books by this author
          $book_count = \Drupal::entityQuery('node')
            ->condition('type', 'book')
            ->condition('field_author', $term->id())
            ->accessCheck(TRUE)
            ->count()
            ->execute();
          
          // Drupal autocomplete expects this format
          $matches[] = [
            'value' => $term_name,
            'label' => $term_name . ' (' . $book_count . ')',
          ];
        }
      }
    }

    return new JsonResponse($matches);
  }

  /**
   * Returns autocomplete suggestions for books.
   */
  public function booksAutocomplete(Request $request) {
    $matches = [];
    $string = $request->query->get('q');

    if ($string) {
      // Query book nodes
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'book')
        ->condition('title', '%' . $string . '%', 'LIKE')
        ->range(0, 10)
        ->sort('title', 'ASC')
        ->accessCheck(TRUE);
      
      $nids = $query->execute();
      
      if (!empty($nids)) {
        $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
        
        foreach ($nodes as $node) {
          $title = $node->getTitle();
          
          // Drupal autocomplete expects this format
          $matches[] = [
            'value' => $title,
            'label' => $title,
          ];
        }
      }
    }

    return new JsonResponse($matches);
  }

}
