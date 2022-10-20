<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an Organizational Unit provider entity type.
 */
interface OunitProviderInterface extends ConfigEntityInterface {

  /**
   * Returns the Institution ID.
   *
   * @return mixed
   *   The Institution ID if it exists, or NULL otherwise.
   */
  public function heiId();

}
