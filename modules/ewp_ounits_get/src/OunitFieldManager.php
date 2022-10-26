<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Organizational Unit field manager service.
 */
class OunitFieldManager implements OunitFieldManagerInterface {

  const IGNORE_FIELDS = [
    'id',
    'status',
  ];

  const IGNORE_FIELD_TYPES = [
    'uuid',
    'language',
    'created',
    'changed',
    'entity_reference',
  ];

  const IGNORE_PROPERTY_TYPES = [
    'map',
  ];

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * JSON data schema.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataSchemaInterface
   */
  protected $dataSchema;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\ewp_ounits_get\JsonDataSchemaInterface $data_schema
   *   JSON data schema.
   */
  public function __construct(
    EntityFieldManagerInterface $entity_field_manager,
    JsonDataSchemaInterface $data_schema
  ) {
    $this->entityFieldManager = $entity_field_manager;
    $this->dataSchema         = $data_schema;
  }

  /**
   * Provides the entity fields for mapping.
   *
   * @return array
   *   The entity fields.
   */
  public function getEntityFields(): array {
    $fields = [];

    // Load the individual entity fields.
    $field_definitions = $this->entityFieldManager
      ->getFieldDefinitions(self::ENTITY_TYPE, self::ENTITY_TYPE);

    foreach ($field_definitions as $field_name => $field_definition) {
      // Skip specific fields.
      if (in_array($field_name, self::IGNORE_FIELDS)) {
        continue;
      }

      $storage_definition = $field_definition->getFieldStorageDefinition();
      $field_type = $storage_definition->getType();

      // Skip specific field types.
      if (in_array($field_type, self::IGNORE_FIELD_TYPES)) {
        continue;
      }

      // Gather information about each field.
      $fields[$field_name]['type'] = $field_type;
      $fields[$field_name]['label'] = $field_definition->getLabel();
      $fields[$field_name]['required'] = $field_definition->isRequired();

      $property_definitions = $storage_definition->getPropertyDefinitions();

      // Gather the data type of each field property.
      foreach ($property_definitions as $name => $definition) {
        $type = $definition->getDataType();

        // Skip specific data types.
        if (!in_array($type, self::IGNORE_PROPERTY_TYPES)) {
          $fields[$field_name]['properties'][$name] = $type;
        }
      }
    }

    return $fields;
  }

  /**
   * Provides the JSON:API attributes for mapping.
   *
   * @return array
   *   The JSON:API attributes.
   */
  public function getJsonAttributes(): array {
    $schema = $this->dataSchema->getSchema();

    // Include the resource ID ahead of everything else.
    $data_type = $schema[JsonDataSchemaInterface::JSONAPI_ID];
    $attributes = [JsonDataSchemaInterface::JSONAPI_ID => $data_type];

    // Gather the attribute data types.
    foreach ($schema[JsonDataSchemaInterface::JSONAPI_ATTR] as $key => $value) {
      $attributes[$key] = $value;
    }

    return $attributes;
  }

  /**
  * Provides the JSON:API attributes as select options.
  *
  * @return array
  *   The JSON:API attributes as select options.
   */
  public function getAttributeOptions(): array {
    $options = [];
    $attributes = $this->getJsonAttributes();

    foreach ($attributes as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $prop => $type) {
          $subkey = implode('.', [$key, $prop]);
          $options[$key][$subkey] = $subkey . ' (' . $type . ')';
        }
      }
      else {
        $options[$key] = $key . ' (' . $value . ')';
      }
    }

    return $options;
  }

}
