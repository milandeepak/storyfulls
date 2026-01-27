<?php

namespace Drupal\storyfulls_banners;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Service for retrieving page banners.
 */
class BannerService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new BannerService.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Gets the banner data for a specific page type.
   *
   * @param string $page_type
   *   The page type (events, blog, books).
   *
   * @return array
   *   An array with 'url' and 'alt' keys, or empty array if no banner found.
   */
  public function getBanner($page_type) {
    $banners = $this->entityTypeManager
      ->getStorage('page_banner')
      ->loadByProperties(['page_type' => $page_type]);

    if (empty($banners)) {
      return [];
    }

    $banner = reset($banners);
    $file = File::load($banner->getBannerImage());

    if (!$file) {
      return [];
    }

    return [
      'url' => $file->createFileUrl(),
      'alt' => $banner->getAltText(),
    ];
  }

}
