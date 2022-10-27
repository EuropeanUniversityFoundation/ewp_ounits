<?php

namespace Drupal\ewp_ounits_get\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Drupal\ewp_ounits_get\OunitFieldManagerInterface;
use Drupal\ewp_ounits_get\OunitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides additional title callbacks for Organizational Unit providers.
 */
class OunitProviderController extends ControllerBase {

  const OUNIT_TYPE      = OunitEntityManagerInterface::ENTITY_TYPE;
  const OUNIT_ID        = OunitEntityManagerInterface::FIELD_ID;
  const OUNIT_CODE      = OunitEntityManagerInterface::FIELD_CODE;
  const HEI_TYPE        = OunitEntityManagerInterface::REFERENCED_TYPE;

  const ENTITY_TYPE   = 'ounit_provider';
  const OPERATION_LINKS = 'operations';

  /**
   * Entity definition for Organizational Unit provider.
   *
   * @var mixed
   */
  protected $definition;

  /**
   * Entity definition for Institution.
   *
   * @var mixed
   */
  protected $heiDefinition;

  /**
   * Entity definition for Organizational Unit.
   *
   * @var mixed
   */
  protected $ounitDefinition;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger,
    OunitEntityManagerInterface $ounit_entity,
    OunitFieldManagerInterface $ounit_fields,
    TranslationInterface $string_translation
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger         = $messenger;
    $this->ounitEntity       = $ounit_entity;
    $this->ounitFields       = $ounit_fields;
    $this->stringTranslation = $string_translation;

    $this->definition = $this->entityTypeManager
      ->getDefinition(self::ENTITY_TYPE);
    $this->heiDefinition = $this->entityTypeManager
      ->getDefinition(self::HEI_TYPE);
    $this->ounitDefinition = $this->entityTypeManager
      ->getDefinition(self::OUNIT_TYPE);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('ewp_ounits_get.entity'),
      $container->get('ewp_ounits_get.fields'),
      $container->get('string_translation'),
    );
  }

  /**
   * Builds a table of Organizational Unit providers with action links.
   *
   * @return array
   *   A render array with the Organizational Unit providers table.
   */
  public function providerList(): array {
    $header = [
      self::ENTITY_TYPE => $this->definition->getLabel(),
      self::HEI_TYPE => $this->heiDefinition->getLabel(),
      self::OPERATION_LINKS => $this->t('Operations'),
    ];

    $providers = $this->entityTypeManager
      ->getStorage(self::ENTITY_TYPE)
      ->loadMultiple();

    $rows = [];

    foreach ($providers as $key => $provider) {
      $hei_exists = $this->ounitEntity
        ->heiIdExists($provider->heiId());

      $row = [
        self::ENTITY_TYPE => $provider->label(),
        self::HEI_TYPE => $this->providerHeiLabel($provider),
        self::OPERATION_LINKS => (!empty($hei_exists))
          ? $this->providerLoadLink($provider)
          : $this->providerEditLink($provider),
      ];

      $rows[] = $row;
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No @providers to display.', [
        '@providers' => $this->definition->getPluralLabel()
      ]),
    ];

    return $build;
  }

  /**
   * Builds a table of Organizational Unit resources with action links.
   *
   * @param \Drupal\ewp_ounits_get\OunitProviderInterface $ounit_provider
   *   The Organizational Unit provider.
   *
   * @return array
   *   A render array with the Organizational Unit providers table.
   */
  public function providerOunitList(OunitProviderInterface $ounit_provider) {
    $provider_link = $this->providerEditLink($ounit_provider);

    $hei_exists = $this->ounitEntity
      ->heiIdExists($ounit_provider->heiId());

    $hei_link = $this->providerHeiLabel($ounit_provider);

    $header = [
      self::OUNIT_TYPE => $this->ounitDefinition->getLabel(),
      self::OUNIT_CODE => $this->t('Code'),
      self::OUNIT_ID => $this->t('ID'),
      self::OPERATION_LINKS => $this->t('Operations'),
    ];

    $rows = [];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No @resources to display.', [
        '@resources' => $this->ounitDefinition->getPluralLabel()
      ]),
    ];

    return $build;
  }

  /**
   * The _title_callback for the Organizational Unit provider edit form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The edit form title.
   */
  public function editFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Edit %ounit_provider Organizational Unit provider', [
      '%ounit_provider' => $ounit_provider->label()
    ]);
  }

  /**
   * The _title_callback for the Organizational Unit provider preview form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The preview form title.
   */
  public function previewFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Preview %ounit_provider Organizational Unit provider', [
      '%ounit_provider' => $ounit_provider->label()
    ]);
  }

  /**
   * The _title_callback for the Organizational Unit provider import form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The import form title.
   */
  public function importFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Import data from %ounit_provider @entity_type', [
      '%ounit_provider' => $ounit_provider->label(),
      '@entity_type' => $this->definition->getSingularLabel(),
    ]);
  }

  /**
   * The _title_callback for the provider's Organizational Unit data list.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The provider's data list title.
   */
  public function ounitListTitle(EntityInterface $ounit_provider) {
    return $this->t('Import @ounits from %ounit_provider.', [
      '@ounits' => $this->ounitDefinition->getPluralLabel(),
      '%ounit_provider' => $ounit_provider->label(),
    ]);
  }

  /**
   * Helper method that provides a link for a provider's JSON:API resource list.
   */
  private function providerLoadLink(OunitProviderInterface $ounit_provider) {
    $text = $this->t('Load data');
    $route = 'entity.ounit.import.provider';
    $params = [self::ENTITY_TYPE => $ounit_provider->id()];
    $options = ['attributes' => ['class' => ['button', 'button--primary']]];

    $url = Url::fromRoute($route, $params, $options);

    return Link::fromTextAndUrl($text, $url);
  }

  /**
   * Helper method that provides a link for a provider's edit form.
   */
  private function providerEditLink(OunitProviderInterface $ounit_provider) {
    $text = $this->t('Edit provider');
    $rel = 'edit-form';
    $options = ['attributes' => ['class' => ['button']]];

    return $ounit_provider->toLink($text, $rel, $options);
  }

  /**
   * Helper method that provides a label for a provider's Institution.
   */
  private function providerHeiLabel(OunitProviderInterface $ounit_provider) {
    $hei_id = $ounit_provider->heiId();
    $hei_exists = $this->ounitEntity->heiIdExists($hei_id);

    foreach ($hei_exists as $id => $hei) { $hei_label = $hei->toLink(); }

    return $hei_label ?? $hei_id;
  }

}
