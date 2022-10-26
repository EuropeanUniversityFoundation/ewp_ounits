<?php

namespace Drupal\ewp_ounits_get;

/**
 * Provides the JSON:API data schema for Organizational Units in OCCAPI.
 */
class OccapiOunitDataSchema implements JsonDataSchemaInterface {

  /**
   * The JSON:API data schema.
   *
   * @var array
   */
  protected $schema;

  /**
   * Defines the JSON:API data schema.
   *
   * @return array
   *   The JSON:API data schema represented as an array.
   */
  public static function schema(): array {
    return [
      JsonDataSchemaInterface::JSONAPI_TYPE => 'string',
      JsonDataSchemaInterface::JSONAPI_ID => 'string',
      JsonDataSchemaInterface::JSONAPI_ATTR => [
        'title' => [
          'string' => 'string',
          'lang' => 'string',
        ],
        'abbreviation' => 'string',
        'ounitId' => 'string',
        'ounitCode' => 'string',
        'url' => [
          'uri' => 'uri',
          'lang' => 'string',
        ],
      ],
      JsonDataSchemaInterface::JSONAPI_LINKS => [
        JsonDataSchemaInterface::JSONAPI_SELF => [
          JsonDataSchemaInterface::JSONAPI_HREF => 'uri',
        ],
      ],
      JsonDataSchemaInterface::REQUIRED_ATTR => [
        'title',
        'ounitId',
        'ounitCode',
      ],
    ];
  }

  /**
   * Provides the JSON:API data schema.
   *
   * @return array
   *   The JSON:API data schema represented as an array.
   */
  public function getSchema(): array {
    if (!isset($this->schema)) {
      $this->schema = static::schema();
    }

    return $this->schema;
  }

}
