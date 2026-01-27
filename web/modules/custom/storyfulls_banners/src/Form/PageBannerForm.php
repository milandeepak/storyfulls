<?php

namespace Drupal\storyfulls_banners\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Form handler for the Page Banner add and edit forms.
 */
class PageBannerForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $page_banner = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Banner Name'),
      '#maxlength' => 255,
      '#default_value' => $page_banner->label(),
      '#description' => $this->t('A descriptive name for this banner (e.g., "Events Page Banner").'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $page_banner->id(),
      '#machine_name' => [
        'exists' => '\Drupal\storyfulls_banners\Entity\PageBanner::load',
      ],
      '#disabled' => !$page_banner->isNew(),
    ];

    $form['page_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Page Type'),
      '#options' => [
        'events' => $this->t('Events Page'),
        'blog' => $this->t('Blog Page'),
        'books' => $this->t('Books Page'),
      ],
      '#default_value' => $page_banner->getPageType(),
      '#required' => TRUE,
      '#description' => $this->t('Select which page this banner is for. Only one banner per page type is allowed.'),
    ];

    $form['banner_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Banner Image'),
      '#default_value' => $page_banner->getBannerImage() ? [$page_banner->getBannerImage()] : NULL,
      '#upload_location' => 'public://banners/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif webp'],
        'file_validate_size' => [10 * 1024 * 1024], // 10MB max
      ],
      '#required' => TRUE,
      '#description' => $this->t('Upload a banner image. Recommended size: 1920x400 pixels.'),
    ];

    $form['alt_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alt Text'),
      '#maxlength' => 255,
      '#default_value' => $page_banner->getAltText(),
      '#required' => TRUE,
      '#description' => $this->t('Alternative text for accessibility.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Check if banner for this page type already exists.
    $page_type = $form_state->getValue('page_type');
    $existing = \Drupal::entityTypeManager()
      ->getStorage('page_banner')
      ->loadByProperties(['page_type' => $page_type]);

    if (!empty($existing)) {
      $existing_banner = reset($existing);
      // Allow editing the same banner.
      if ($this->entity->isNew() || $existing_banner->id() !== $this->entity->id()) {
        $form_state->setErrorByName('page_type', $this->t('A banner for @page_type already exists. Please edit the existing banner instead.', [
          '@page_type' => $form['page_type']['#options'][$page_type],
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $page_banner = $this->entity;
    
    // Handle file upload.
    $banner_image = $form_state->getValue('banner_image');
    if (!empty($banner_image[0])) {
      $page_banner->set('banner_image', $banner_image[0]);
    }

    $status = $page_banner->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label banner.', [
        '%label' => $page_banner->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Updated the %label banner.', [
        '%label' => $page_banner->label(),
      ]));
    }

    $form_state->setRedirect('entity.page_banner.collection');
    return $status;
  }

}
