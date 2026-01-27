<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController extends ControllerBase {
  public function authors(Request $request) {
    // Accept both 'term' (jQuery UI default) and 'q' (custom)
    $term = $request->query->get('term') ?: $request->query->get('q');
    
    if (empty($term)) {
      return new JsonResponse([]);
    }
    
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'author')
      ->condition('name', $term, 'CONTAINS')
      ->range(0, 10)
      ->accessCheck(TRUE);
    $tids = $query->execute();
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
    $results = [];
    foreach ($terms as $t) {
      // Format expected by jQuery UI Autocomplete
      $results[] = [
        'label' => $t->getName(),
        'value' => $t->getName() . ' (' . $t->id() . ')'
      ];
    }
    return new JsonResponse($results);
  }

  public function books(Request $request) {
    // Accept both 'term' (jQuery UI default) and 'q' (custom)
    $term = $request->query->get('term') ?: $request->query->get('q');
    
    if (empty($term)) {
      return new JsonResponse([]);
    }
    
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'book')
      ->condition('title', $term, 'CONTAINS')
      ->range(0, 10)
      ->accessCheck(TRUE);
    $nids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    $results = [];
    foreach ($nodes as $n) {
      $results[] = [
        'label' => $n->getTitle(),
        'value' => $n->getTitle() . ' (' . $n->id() . ')'
      ];
    }
    return new JsonResponse($results);
  }
}
