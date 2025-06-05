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
  public function getEntityFields(): array;

  /**
   * Provides the JSON:API attributes for mapping.
   *
   * @return array
   *   The JSON:API attributes.
   */
  public function getJsonAttributes(): array;

  /**
   * Provides the JSON:API attributes as select options.
   *
   * @return array
   *   The JSON:API attributes as select options.
   */
  public function getAttributeOptions(): array;

  /**
   * Converts JSON:API attributes to Organizational Unit data.
   *
   * @param array $attributes
   *   The JSON:API attributes.
   *
   * @return array
   *   The Organizational Unit data.
   */
  public function prepareOunitData(array $attributes): array;

}
