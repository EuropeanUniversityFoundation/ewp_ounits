<?php

namespace Drupal\ewp_ounits\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Organizational Unit entities.
 *
 * @ingroup ewp_ounits
 */
interface OunitInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Organizational Unit name.
   *
   * @return string
   *   Name of the Organizational Unit.
   */
  public function getName();

  /**
   * Sets the Organizational Unit name.
   *
   * @param string $name
   *   The Organizational Unit name.
   *
   * @return \Drupal\ewp_ounits\Entity\OunitInterface
   *   The called Organizational Unit entity.
   */
  public function setName($name);

  /**
   * Gets the Organizational Unit creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Organizational Unit.
   */
  public function getCreatedTime();

  /**
   * Sets the Organizational Unit creation timestamp.
   *
   * @param int $timestamp
   *   The Organizational Unit creation timestamp.
   *
   * @return \Drupal\ewp_ounits\Entity\OunitInterface
   *   The called Organizational Unit entity.
   */
  public function setCreatedTime($timestamp);

}
