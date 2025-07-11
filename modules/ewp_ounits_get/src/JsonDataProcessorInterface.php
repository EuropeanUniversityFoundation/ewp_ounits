<?php

namespace Drupal\ewp_ounits_get;

/**
 * Defines an interface for a JSON:API data processor.
 */
interface JsonDataProcessorInterface {

  /**
   * Get the data from a resource.
   *
   * @param array $resource
   *   An array containing a JSON:API resource.
   *
   * @return array
   *   The actual data of the JSON:API resource.
   */
  public function getResourceData(array $resource): array;

  /**
   * Get a resource type.
   *
   * @param array $resource
   *   An array containing a JSON:API resource data.
   *
   * @return string
   *   The type of the JSON:API resource.
   */
  public function getResourceType(array $resource): string;

  /**
   * Get a resource ID.
   *
   * @param array $resource
   *   An array containing a JSON:API resource data.
   *
   * @return string
   *   The ID of the JSON:API resource.
   */
  public function getResourceId(array $resource): string;

  /**
   * Get a resource title.
   *
   * @param array $resource
   *   An array containing a JSON:API resource data.
   *
   * @return string
   *   The title of the JSON:API resource.
   */
  public function getResourceTitle(array $resource): string;

  /**
   * Get a resource attribute by key.
   *
   * @param array $resource
   *   An array containing a JSON:API resource data.
   * @param string $attribute
   *   The key to a JSON:API resource attribute.
   *
   * @return array
   *   The value of the attribute keyed by attribute name.
   */
  public function getResourceAttribute(array $resource, string $attribute): array;

  /**
   * Get a resource link by key.
   *
   * @param array $resource
   *   An array containing a JSON:API resource data.
   * @param string $link_type
   *   The JSON:API link type key to extract.
   *
   * @return string
   *   The URL of the JSON:API link.
   */
  public function getResourceLinkByType(array $resource, string $link_type): string;

  /**
   * Gather resource collection links.
   *
   * @param array $collection
   *   An array containing a JSON:API resource collection.
   *
   * @return array
   *   An array of resource 'self' links keyed by resource ID.
   */
  public function getResourceLinks(array $collection): array;

  /**
   * Sort language typed data by preferred language.
   *
   * @param array $language_typed_data
   *   An array containing language typed data.
   *
   * @return array
   *   The sorted data.
   */
  public function sortByLang(array $language_typed_data): array;

}
