<?php

namespace Drupal\ewp_ounits_get\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ewp_ounits_get\JsonDataFetcherInterface;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Drupal\ewp_ounits_get\OunitFieldManagerInterface;
use Drupal\ewp_ounits_get\OunitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Returns responses for EWP OUnits GET routes.
 */
class OunitImportController extends ControllerBase {

  const JSONAPI_COLLECTION_URL = 'collection_url';
  const JSONAPI_RESOURCE_TYPE  = 'ounit';
  const JSONAPI_DATA_KEY       = 'data';
  const JSONAPI_ATTR_KEY       = 'attributes';

  const ENTITY_TYPE     = OunitEntityManagerInterface::ENTITY_TYPE;
  const FIELD_ID        = OunitEntityManagerInterface::FIELD_ID;
  const FIELD_CODE      = OunitEntityManagerInterface::FIELD_CODE;
  const REFERENCED_TYPE = OunitEntityManagerInterface::REFERENCED_TYPE;

  const PROVIDER_TYPE = 'ounit_provider';

  /**
   * Parent Institution entity.
   *
   * @var array
   */
  protected $heiExists;

  /**
   * SharedTempStore key.
   *
   * @var string
   */
  protected $tempStoreKey;

  /**
   * JSON:API endpoint.
   *
   * @var string
   */
  protected $endpoint;

  /**
   * Organizational Unit data.
   *
   * @var array
   */
  protected $ounitData;

  /**
   * A router implementation which does not check access.
   *
   * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
   */
  protected $accessUnawareRouter;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * JSON data fetcher.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataFetcherInterface
   */
  protected $jsonDataFetcher;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The ewp_ounits_get.entity service.
   *
   * @var \Drupal\ewp_ounits_get\OunitEntityManagerInterface
   */
  protected $ounitEntity;

  /**
   * The ewp_ounits_get.fields service.
   *
   * @var \Drupal\ewp_ounits_get\OunitFieldManagerInterface
   */
  protected $ounitFields;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\ewp_ounits_get\JsonDataFetcherInterface $json_data_fetcher
   *   The JSON data fetcher.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\ewp_ounits_get\OunitEntityManagerInterface $ounit_entity
   *   The ewp_ounits_get.entity service.
   * @param \Drupal\ewp_ounits_get\OunitFieldManagerInterface $ounit_fields
   *   The ewp_ounits_get.fields service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Symfony\Component\Routing\Matcher\UrlMatcherInterface $access_unaware_router
   *   A router implementation which does not check access.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    JsonDataFetcherInterface $json_data_fetcher,
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger,
    OunitEntityManagerInterface $ounit_entity,
    OunitFieldManagerInterface $ounit_fields,
    TranslationInterface $string_translation,
    UrlMatcherInterface $access_unaware_router
  ) {
    $this->entityTypeManager   = $entity_type_manager;
    $this->jsonDataFetcher     = $json_data_fetcher;
    $this->logger              = $logger_factory->get('ewp_ounits_get');
    $this->messenger           = $messenger;
    $this->ounitEntity         = $ounit_entity;
    $this->ounitFields         = $ounit_fields;
    $this->stringTranslation   = $string_translation;
    $this->accessUnawareRouter = $access_unaware_router;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('ewp_ounits_get.fetch'),
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('ewp_ounits_get.entity'),
      $container->get('ewp_ounits_get.fields'),
      $container->get('string_translation'),
      $container->get('router.no_access_checks'),
    );
  }

  /**
   * Builds the response for a selected provider and ounit_id.
   */
  public function ounitImport(Request $request, OunitProviderInterface $ounit_provider, string $ounit_id) {
    $this->setData($ounit_provider, $ounit_id);

    $error = $this->checkErrors($ounit_provider, $ounit_id);

    if (empty($error)) {
      $attributes = $this->ounitData[self::JSONAPI_ATTR_KEY];
      /** @disregard P1013 */
      $entity_data = $this->ounitFields->prepareOunitData($attributes);

      foreach ($this->heiExists as $id => $entity) {
        $entity_data[OunitEntityManagerInterface::ENTITY_REFERENCE][] = [
          'target_id' => $id,
        ];
      }

      $created = $this->ounitEntity
        ->create($ounit_provider->heiId(), $entity_data);

      if (!empty($created)) {
        foreach ($created as $entity) {
          $message = $this->t('Imported %ounit', [
            '%ounit' => $entity->toLink()->toString(),
          ]);
        }
        $this->messenger->addMessage($message);
      }
    }
    else {
      $this->messenger->addError($error);
    }

    $referer = $request->headers->get('referer');
    $result = $this->accessUnawareRouter->match($referer);
    $params = [self::PROVIDER_TYPE => $ounit_provider->id()];

    return $this->redirect($result['_route'], $params);
  }

  /**
   * Set data for persistency.
   */
  public function setData(OunitProviderInterface $ounit_provider, string $ounit_id) {
    if (!isset($this->heiExists)) {
      $this->heiExists = $this->ounitEntity
        ->heiIdExists($ounit_provider->heiId());
    }

    if (!isset($this->tempStoreKey)) {
      $this->tempStoreKey = implode('.', [
        self::JSONAPI_RESOURCE_TYPE,
        $ounit_provider->id(),
      ]);
    }

    if (!isset($this->endpoint)) {
      $this->endpoint = $ounit_provider->get(self::JSONAPI_COLLECTION_URL);
    }

    if (!isset($this->ounitData)) {
      $json_data = $this->jsonDataFetcher
        ->load($this->tempStoreKey, $this->endpoint);

      $collection = \json_decode($json_data, TRUE);

      foreach ($collection[self::JSONAPI_DATA_KEY] ?? [] as $resource) {
        if ($resource['id'] === $ounit_id) {
          $this->ounitData = $resource;
        }
      }
    }
  }

  /**
   * Check errors.
   */
  public function checkErrors(OunitProviderInterface $ounit_provider, string $ounit_id) {
    $ounit_id_exists = $this->ounitEntity
      ->ounitIdExists($ounit_provider->heiId(), $ounit_id);

    if (!empty($ounit_id_exists)) {
      foreach ($ounit_id_exists as $entity) {
        return $this->t('ID %ounit_id already exists: @link.', [
          '%ounit_id' => $ounit_provider->heiId(),
          '@link' => $entity->toLink(),
        ]);
      }
    }

    if (empty($this->heiExists)) {
      return $this->t('Missing Institution with ID %hei_id.', [
        '%hei_id' => $ounit_provider->heiId(),
      ]);
    }

    if (empty($this->ounitData)) {
      return $this->t('Missing data for ID %ounit_id.', [
        '%ounit_id' => $ounit_id,
      ]);
    }

    return NULL;
  }

}
