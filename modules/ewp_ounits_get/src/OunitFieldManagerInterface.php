<?php

namespace Drupal\ewp_ounits_get;

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

  /**
   * Provides the JSON:API attributes for mapping.
   *
   * @return array
   *   The JSON:API attributes.
   */
  public function getJsonAttributes();

  /**
  * Provides the JSON:API attributes as select options.
  *
  * @return array
  *   The JSON:API attributes as select options.
   */
  public function getAttributeOptions();

}
