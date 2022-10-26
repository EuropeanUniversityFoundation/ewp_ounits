<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * JSON data fetching service.
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
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    Client $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    SharedTempStoreFactory $temp_store_factory,
    TranslationInterface $string_translation
  ) {
    $this->httpClient         = $http_client;
    $this->logger             = $logger_factory->get('ewp_ounits_get');
    $this->tempStore          = $temp_store_factory->get('ewp_ounits_get');
    $this->stringTranslation  = $string_translation;
  }

  /**
   * Load JSON:API data from tempstore or external API endpoint.
   *
   * @param string $temp_store_key
   *   A key from the key_value_expire table.
   * @param string $endpoint
   *   The endpoint from which to fetch data.
   * @param boolean $refresh
   *   Whether to force a refresh of the stored data.
   *
   * @return string|null
   *   A string containing the stored data or NULL.
   */
  public function load(string $temp_store_key, string $endpoint, $refresh = FALSE): ?string {
    // If tempstore is empty OR should be refreshed.
    if (empty($this->tempStore->get($temp_store_key)) || $refresh) {
      // Get the data from the provided endpoint and store it.
      $this->tempStore->set($temp_store_key, $this->get($endpoint));
      $message = $this->t("Loaded @key into temporary storage", [
        '@key' => $temp_store_key
      ]);
      $this->logger->notice($message);
    }

    // Retrieve whatever is in storage.
    $data = $this->tempStore->get($temp_store_key);
    // Process the data as needed.
    $processed = (!empty($data)) ? $this->process($data) : NULL;

    return $processed;
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
    } catch (GuzzleException $e) {
      $response = $e->getResponse()->getBody();
    } catch (Exception $e) {
      watchdog_exception('ewp_ounits_get', $e->getMessage());
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
    } catch (GuzzleException $e) {
      $code = $e->getCode();
    } catch (Exception $e) {
      watchdog_exception('ewp_ounits_get', $e->getMessage());
    }

    return $code;
  }

  /**
   * Process JSON:API data before validation.
   *
   * @param string $data
   *   The original data.
   *
   * @return string
   *   The processed data.
   */
  protected function process(string $data): string {
    $processed = $data;

    $replacements = [
      'ouint' => 'ounit',
    ];

    foreach ($replacements as $wrong => $right) {
      $processed = str_replace($wrong, $right, $processed, $count);
    }

    return $processed;
  }

}
