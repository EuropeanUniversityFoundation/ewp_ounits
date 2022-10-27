<?php

namespace Drupal\ewp_ounits_get\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Drupal\ewp_ounits_get\OunitFieldManagerInterface;
use Drupal\ewp_ounits_get\OunitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for EWP OUnits GET routes.
 */
class OunitImportController extends ControllerBase {

  const ENTITY_TYPE     = OunitEntityManagerInterface::ENTITY_TYPE;
  const FIELD_ID        = OunitEntityManagerInterface::FIELD_ID;
  const FIELD_CODE      = OunitEntityManagerInterface::FIELD_CODE;
  const REFERENCED_TYPE = OunitEntityManagerInterface::REFERENCED_TYPE;

  const PROVIDER_TYPE   = 'ounit_provider';
  const OPERATION_LINKS = 'operations';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
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
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger,
    OunitEntityManagerInterface $ounit_entity,
    OunitFieldManagerInterface $ounit_fields,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger            = $logger_factory->get('ewp_ounits_get');
    $this->messenger         = $messenger;
    $this->ounitEntity       = $ounit_entity;
    $this->ounitFields       = $ounit_fields;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('ewp_ounits_get.entity'),
      $container->get('ewp_ounits_get.fields'),
      $container->get('string_translation'),
    );
  }

  /**
   * Builds the response for a selected provider and ounit_id.
   */
  public function ounitImport(OunitProviderInterface $ounit_provider, string $ounit_id) {
    dpm($ounit_id);
    dpm($ounit_provider);

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
