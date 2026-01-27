<?php

namespace Drupal\storyfulls_banners;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;

/**
 * Provides a listing of Page Banners.
 */
class PageBannerListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Banner Name');
    $header['page_type'] = $this->t('Page Type');
    $header['image'] = $this->t('Banner Image');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    
    $page_types = [
      'events' => $this->t('Events Page'),
      'blog' => $this->t('Blog Page'),
      'books' => $this->t('Books Page'),
    ];
    $row['page_type'] = $page_types[$entity->getPageType()] ?? $entity->getPageType();
    
    // Show thumbnail of banner image.
    $image_html = '';
    if ($entity->getBannerImage()) {
      $file = File::load($entity->getBannerImage());
      if ($file) {
        $image_html = '<img src="' . $file->createFileUrl() . '" style="max-width: 200px; max-height: 50px;" alt="' . htmlspecialchars($entity->getAltText()) . '" />';
      }
    }
    $row['image'] = ['data' => ['#markup' => $image_html]];
    
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    
    // Ensure edit and delete operations are present.
    if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      ];
    }
    
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 20,
        'url' => $entity->toUrl('delete-form'),
      ];
    }
    
    return $operations;
  }

}
