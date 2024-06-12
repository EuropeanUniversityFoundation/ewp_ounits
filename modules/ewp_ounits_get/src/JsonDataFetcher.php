<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Core\Utility\Error;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * JSON data fetcher.
 */
class JsonDataFetcher implements JsonDataFetcherInterface {

  use StringTranslationTrait;

  /**
   * HTTP Client for API calls.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The module handler to invoke the alter hooks with.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * An instance of the key/value store.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * Constructs a new JsonDataFetcher.
   *
   * @param \GuzzleHttp\Client $http_client
   *   HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hooks with.
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    Client $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    ModuleHandlerInterface $module_handler,
    SharedTempStoreFactory $temp_store_factory,
    TranslationInterface $string_translation
  ) {
    $this->httpClient        = $http_client;
    $this->logger            = $logger_factory->get('ewp_ounits_get');
    $this->moduleHandler     = $module_handler;
    $this->tempStore         = $temp_store_factory->get('ewp_ounits_get');
    $this->stringTranslation = $string_translation;
  }

  /**
   * Load JSON:API data from tempstore or external API endpoint.
   *
   * @param string $temp_store_key
   *   A key from the key_value_expire table.
   * @param string $endpoint
   *   The endpoint from which to fetch data.
   * @param bool $refresh
   *   Whether to force a refresh of the stored data.
   *
   * @return string|null
   *   A string containing the stored data or NULL.
   */
  public function load(string $temp_store_key, string $endpoint, $refresh = FALSE): ?string {
    $context = ['unalterable' => $temp_store_key];
    // If tempstore is empty OR should be refreshed.
    if (empty($this->tempStore->get($temp_store_key)) || $refresh) {
      // Get the data from the provided endpoint.
      $raw = $this->get($endpoint);
      // Allow other modules to alter the raw data before saving it.
      $this->moduleHandler->alter('ounit_data_get', $raw, $context);
      // Save the data to tempstore.
      $this->tempStore->set($temp_store_key, $raw);
      $message = $this->t("Loaded @key into temporary storage", [
        '@key' => $temp_store_key,
      ]);
      $this->logger->notice($message);
    }

    // Retrieve whatever is in storage.
    $data = $this->tempStore->get($temp_store_key);
    // Allow other modules to alter the tempstore data before serving it.
    $this->moduleHandler->alter('ounit_data_load', $data, $context);

    return $data;
  }

  /**
   * Get JSON:API data from an external API endpoint.
   *
   * @param string $endpoint
   *   The endpoint from which to fetch data.
   *
   * @return string
   *   A string containing JSON data.
   */
  public function get(string $endpoint): string {
    // Prepare the JSON string.
    $json_data = '';

    $response = NULL;

    // Build the HTTP request.
    try {
      $request = $this->httpClient->get($endpoint);
      $response = $request->getBody();
    }
    catch (GuzzleException $e) {
      /** @disregard P1013 */
      $response = $e->getResponse()->getBody();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    // Extract the data from the Guzzle Stream.
    $decoded = json_decode($response, TRUE);
    // Encode the data for persistency.
    $json_data = json_encode($decoded);

    // Return the data.
    return $json_data;
  }

  /**
   * Get response code from an external API endpoint.
   *
   * @param string $endpoint
   *   The external API endpoint.
   *
   * @return int
   *   The response code.
   */
  public function getResponseCode(string $endpoint): int {
    // Build the HTTP request.
    try {
      $request = $this->httpClient->get($endpoint);
      $code = $request->getStatusCode();
    }
    catch (GuzzleException $e) {
      $code = $e->getCode();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    return $code;
  }

}
