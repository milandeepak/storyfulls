<?php

namespace Drupal\storyfulls_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Controller for book review modal.
 */
class BookReviewModalController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a BookReviewModalController object.
   */
  public function __construct(FormBuilderInterface $form_builder, RendererInterface $renderer) {
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * Returns the review form HTML.
   */
  public function getForm($book_id) {
    // Build the form
    $form = $this->formBuilder->getForm('Drupal\storyfulls_profile\Form\BookReviewForm', $book_id);
    
    // Render the form
    $html = $this->renderer->renderRoot($form);
    
    // Return plain HTML response
    return new \Symfony\Component\HttpFoundation\Response($html);
  }

}
