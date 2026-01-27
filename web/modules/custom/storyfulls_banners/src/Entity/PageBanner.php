<?php

namespace Drupal\storyfulls_banners\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Page Banner entity.
 *
 * @ConfigEntityType(
 *   id = "page_banner",
 *   label = @Translation("Page Banner"),
 *   handlers = {
 *     "list_builder" = "Drupal\storyfulls_banners\PageBannerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\storyfulls_banners\Form\PageBannerForm",
 *       "edit" = "Drupal\storyfulls_banners\Form\PageBannerForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "page_banner",
 *   admin_permission = "administer page banners",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "page_type",
 *     "banner_image",
 *     "alt_text"
 *   },
 *   links = {
 *     "collection" = "/admin/content/banners",
 *     "add-form" = "/admin/content/banners/add",
 *     "edit-form" = "/admin/content/banners/{page_banner}/edit",
 *     "delete-form" = "/admin/content/banners/{page_banner}/delete"
 *   }
 * )
 */
class PageBanner extends ConfigEntityBase {

  /**
   * The banner ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The banner label.
   *
   * @var string
   */
  protected $label;

  /**
   * The page type (events, blog, books).
   *
   * @var string
   */
  protected $page_type;

  /**
   * The banner image file ID.
   *
   * @var int
   */
  protected $banner_image;

  /**
   * The alt text for the banner.
   *
   * @var string
   */
  protected $alt_text;

  /**
   * Gets the page type.
   *
   * @return string
   */
  public function getPageType() {
    return $this->page_type;
  }

  /**
   * Gets the banner image file ID.
   *
   * @return int
   */
  public function getBannerImage() {
    return $this->banner_image;
  }

  /**
   * Gets the alt text.
   *
   * @return string
   */
  public function getAltText() {
    return $this->alt_text;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    
    // Make the banner image permanent.
    if ($this->banner_image) {
      $file = \Drupal::entityTypeManager()
        ->getStorage('file')
        ->load($this->banner_image);
      if ($file) {
        $file->setPermanent();
        $file->save();
        \Drupal::service('file.usage')->add($file, 'storyfulls_banners', 'page_banner', $this->id());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    
    // Delete file usage when banner is deleted.
    foreach ($entities as $entity) {
      if ($entity->banner_image) {
        $file = \Drupal::entityTypeManager()
          ->getStorage('file')
          ->load($entity->banner_image);
        if ($file) {
          \Drupal::service('file.usage')->delete($file, 'storyfulls_banners', 'page_banner', $entity->id());
        }
      }
    }
  }

}
