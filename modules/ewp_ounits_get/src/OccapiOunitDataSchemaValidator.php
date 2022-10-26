<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Validates JSON:API data against the OCCAPI OUnit schema.
 */
class OccapiOunitDataSchemaValidator implements JsonDataSchemaValidatorInterface {

  use StringTranslationTrait;

  const JSONAPI_DATA = JsonDataSchemaInterface::JSONAPI_DATA;
  const JSONAPI_ATTR = JsonDataSchemaInterface::JSONAPI_ATTR;
  const REQUIRED_ATTR = JsonDataSchemaInterface::REQUIRED_ATTR;

  /**
   * JSON data schema.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataSchemaInterface
   */
  protected $dataSchema;

  /**
   * The constructor.
   *
   * @param \Drupal\ewp_ounits_get\JsonDataSchemaInterface $data_schema
   *   JSON data schema.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    JsonDataSchemaInterface $data_schema,
    TranslationInterface $string_translation
  ) {
    $this->dataSchema        = $data_schema;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Validate JSON:API data against a schema.
   *
   * @param array $data
   *   An array containing JSON:API data.
   *
   * @return array
   *   Validation errors, if any exist; otherwise returns an empty array.
   */
  public function validateSchema(array $data): array {
    $errors = [];

    $schema = $this->dataSchema->getSchema();

    // Check for missing primary keys.
    $missing_keys = [];

    foreach ($schema as $key => $value) {
      if ($key !== self::REQUIRED_ATTR && !array_key_exists($key, $data)) {
        $missing_keys[] = $key;
      }
    }

    if (!empty($missing_keys)) {
      $errors[] = $this->t('%id: Missing primary keys %missing.', [
        '%id' => $data[JsonDataSchemaInterface::JSONAPI_ID],
        '%missing' => implode(', ', $missing_keys),
      ]);
    }

    // Check for missing required attributes.
    $missing_attr = [];

    if (array_key_exists(self::JSONAPI_ATTR, $data)) {
      foreach ($schema[self::JSONAPI_ATTR] as $attribute => $value) {
        $required = in_array(self::JSONAPI_ATTR, $schema[self::REQUIRED_ATTR]);
        $exists = array_key_exists($attribute, $data[self::JSONAPI_ATTR]);

        if ($required && !$exists) {
          $missing_attr[] = $attribute;
        }
      }
    }
    else {
      $missing_attr = self::REQUIRED_ATTR;
    }

    if (!empty($missing_attr)) {
      $errors[] = $this->t('%id: Missing required data attributes %missing.', [
        '%id' => $data[JsonDataSchemaInterface::JSONAPI_ID],
        '%missing' => implode(', ', $missing_attr)
      ]);
    }

    return $errors;
  }
}
