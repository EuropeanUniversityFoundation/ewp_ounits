<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Defines an interface for an Organizational Unit field manager.
 */
interface OunitFieldManagerInterface {

  const ENTITY_TYPE = 'ounit';

  /**
   * Provides the entity fields for mapping.
   *
   * @return array
   *   The entity fields.
   */
  public function getEntityFields();

}
