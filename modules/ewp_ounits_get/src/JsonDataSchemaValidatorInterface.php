<?php

namespace Drupal\ewp_ounits_get;

/**
 * Defines an interface for JSON:API data schema validation.
 */
interface JsonDataSchemaValidatorInterface {

  /**
   * Validate JSON:API data against a schema.
   *
   * @param array $data
   *   An array containing JSON:API data.
   *
   * @return array
   *   Validation errors, if any exist; otherwise returns an empty array.
   */
  public function validateSchema(array $data);

}
