<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Organizational Unit field manager.
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
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * JSON data processor.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataProcessorInterface
   */
  protected $jsonDataProcessor;

  /**
   * JSON data schema.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataSchemaInterface
   */
  protected $dataSchema;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\ewp_ounits_get\JsonDataProcessorInterface $json_data_processor
   *   JSON data schema.
   * @param \Drupal\ewp_ounits_get\JsonDataSchemaInterface $data_schema
   *   JSON data schema.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityFieldManagerInterface $entity_field_manager,
    JsonDataProcessorInterface $json_data_processor,
    JsonDataSchemaInterface $data_schema,
  ) {
    $this->configFactory      = $config_factory;
    $this->entityFieldManager = $entity_field_manager;
    $this->jsonDataProcessor  = $json_data_processor;
    $this->dataSchema         = $data_schema;
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
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
   * {@inheritdoc}
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

  /**
   * {@inheritdoc}
   */
  public function prepareOunitData(array $attributes): array {
    $data = [];
    $tree = [];

    $fieldmap = $this->configFactory
      ->get('ewp_ounits_get.fieldmap')
      ->get('field_mapping');

    foreach ($fieldmap as $field => $source) {
      $field_keys = explode('__', $field);
      $source_keys = explode('__', $source);

      $field_name = $field_keys[0];
      $field_prop = $field_keys[1];

      $tree[$field_name][$field_prop] = (count($source_keys) > 1)
        ? [$source_keys[0] => $source_keys[1]]
        : $source_keys[0];
    }

    foreach ($tree as $field => $properties) {
      foreach ($properties as $property => $source) {
        if (!is_array($source)) {
          if (!empty($attributes[$source])) {
            $data[$field][$property] = $attributes[$source];
          }
        }
        else {
          foreach ($source as $src_field => $src_property) {
            if (!empty($attributes[$src_field])) {
              $arr_keys = array_keys($attributes[$src_field]);
              $str_keys = array_filter($arr_keys, 'is_string');
              $num_only = (count($str_keys) === 0);

              if ($num_only) {
                $sorted = $this->jsonDataProcessor
                  ->sortByLang($attributes[$src_field]);

                foreach ($sorted as $i => $item) {
                  foreach ($item as $item_key => $item_value) {
                    if ($src_property === $item_key) {
                      $data[$field][$i][$property] = $item_value;
                    }
                  }
                }
              }
              else {
                foreach ($attributes[$src_field] as $item_key => $item_value) {
                  if ($src_property === $item_key) {
                    $data[$field][$property] = $item_value;
                  }
                }
              }
            }
          }
        }
      }
    }

    $field_definitions = $this->entityFieldManager
      ->getFieldDefinitions(self::ENTITY_TYPE, self::ENTITY_TYPE);

    foreach ($field_definitions as $field_name => $definition) {
      if (array_key_exists($field_name, $data)) {
        $max = $definition->getFieldStorageDefinition()->getCardinality();

        $arr_keys = array_keys($data[$field_name]);
        $str_keys = array_filter($arr_keys, 'is_string');
        $num_only = (count($str_keys) === 0);

        if ($num_only && $max === 1) {
          $data[$field_name] = $data[$field_name][0];
        }
      }
    }

    return $data;
  }

}
