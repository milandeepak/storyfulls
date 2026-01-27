<?php

namespace Drupal\storyfulls_profile\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Form for submitting book reviews.
 */
class BookReviewForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a BookReviewForm object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_review_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $book_id = NULL) {
    // Store book ID in form state
    $form_state->set('book_id', $book_id);

    // Load the book to get title
    $book = NULL;
    $book_title = 'this book';
    if ($book_id) {
      $book = $this->entityTypeManager->getStorage('node')->load($book_id);
      if ($book) {
        $book_title = $book->getTitle();
      }
    }

    // Check if user already reviewed this book
    $existing_review = $this->getUserReview($book_id, $this->currentUser->id());
    
    if ($existing_review) {
      $form['error_message'] = [
        '#markup' => '<div class="review-form-error">You have already reviewed this book. You can only submit one review per book.</div>',
      ];
      return $form;
    }

    $form['book_title_display'] = [
      '#markup' => '<h3 class="review-form-title">Review: ' . $book_title . '</h3>',
    ];

    $form['rating'] = [
      '#type' => 'select',
      '#title' => $this->t('Rating'),
      '#options' => [
        '' => $this->t('- Select rating -'),
        '5' => $this->t('⭐⭐⭐⭐⭐ (5 stars - Loved it!)'),
        '4' => $this->t('⭐⭐⭐⭐ (4 stars - Really liked it)'),
        '3' => $this->t('⭐⭐⭐ (3 stars - It was okay)'),
        '2' => $this->t('⭐⭐ (2 stars - Didn\'t like it)'),
        '1' => $this->t('⭐ (1 star - Disliked it)'),
      ],
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['review-rating-select'],
      ],
    ];

    $form['review_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your Review'),
      '#required' => TRUE,
      '#rows' => 5,
      '#attributes' => [
        'placeholder' => $this->t('Share your thoughts about this book...'),
        'class' => ['review-text-area'],
      ],
      '#description' => $this->t('Tell us what you thought about the book. What did you like? What did you learn?'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Review'),
      '#attributes' => [
        'class' => ['review-submit-btn'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check if user is logged in
    if ($this->currentUser->isAnonymous()) {
      $form_state->setErrorByName('', $this->t('You must be logged in to submit a review.'));
      return;
    }

    $book_id = $form_state->get('book_id');
    
    // Validate book exists
    if (!$book_id) {
      $form_state->setErrorByName('', $this->t('Invalid book.'));
      return;
    }

    $book = $this->entityTypeManager->getStorage('node')->load($book_id);
    if (!$book || $book->getType() !== 'book') {
      $form_state->setErrorByName('', $this->t('Invalid book.'));
      return;
    }

    // Check for duplicate review
    $existing_review = $this->getUserReview($book_id, $this->currentUser->id());
    if ($existing_review) {
      $form_state->setErrorByName('', $this->t('You have already reviewed this book.'));
      return;
    }

    // Validate rating
    $rating = $form_state->getValue('rating');
    if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
      $form_state->setErrorByName('rating', $this->t('Please select a valid rating.'));
    }

    // Validate review text
    $review_text = trim($form_state->getValue('review_text'));
    if (empty($review_text)) {
      $form_state->setErrorByName('review_text', $this->t('Please write a review.'));
    }
    
    if (strlen($review_text) < 10) {
      $form_state->setErrorByName('review_text', $this->t('Your review must be at least 10 characters long.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $book_id = $form_state->get('book_id');
    $rating = $form_state->getValue('rating');
    $review_text = trim($form_state->getValue('review_text'));

    // Load the book to get title for review node title
    $book = $this->entityTypeManager->getStorage('node')->load($book_id);
    $book_title = $book ? $book->getTitle() : 'Unknown Book';

    // Create the review node
    $review = Node::create([
      'type' => 'book_review',
      'title' => 'Review of ' . $book_title . ' by ' . $this->currentUser->getDisplayName(),
      'uid' => $this->currentUser->id(),
      'status' => 1,
      'field_reviewed_book' => ['target_id' => $book_id],
      'field_rating' => $rating,
      'field_review_text' => $review_text,
    ]);

    $review->save();

    // 1. Drupal messenger (green box at top)
    $this->messenger()->addStatus($this->t('Thank you! Your review has been submitted successfully.'));
    
    // 2. Session flag for custom success message banner
    $_SESSION['review_submitted'] = [
      'book_id' => $book_id,
      'review_id' => $review->id(),
      'timestamp' => time(),
    ];
    
    // 3. Session flag for toast notification
    $_SESSION['show_review_toast'] = TRUE;
    
    // Redirect back to the book page
    $form_state->setRedirect('entity.node.canonical', ['node' => $book_id]);
  }

  /**
   * Check if user has already reviewed a book.
   */
  protected function getUserReview($book_id, $user_id) {
    if (!$book_id || !$user_id) {
      return NULL;
    }

    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'book_review')
      ->condition('uid', $user_id)
      ->condition('field_reviewed_book', $book_id)
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->range(0, 1);

    $result = $query->execute();
    
    return !empty($result) ? reset($result) : NULL;
  }

}
