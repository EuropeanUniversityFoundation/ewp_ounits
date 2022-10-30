<?php

namespace Drupal\ewp_ounits_get\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Drupal\ewp_ounits_get\OunitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides additional title callbacks for Organizational Unit providers.
 */
class OunitProviderListController extends ControllerBase {

  const ENTITY_TYPE     = 'ounit_provider';
  const HEI_TYPE        = OunitEntityManagerInterface::REFERENCED_TYPE;
  const OPERATION_LINKS = 'operations';

  /**
   * The current user.
   */
  protected $currentUser;

  /**
   * Entity definition for Organizational Unit provider.
   *
   * @var mixed
   */
  protected $definition;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Organizational Unit entity manager.
   *
   * @var \Drupal\ewp_ounits_get\OunitEntityManagerInterface
   */
  protected $ounitEntity;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   A proxied implementation of AccountInterface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\ewp_ounits_get\OunitEntityManagerInterface $ounit_entity
   *   The Organizational Unit entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    AccountProxy $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    OunitEntityManagerInterface $ounit_entity,
    TranslationInterface $string_translation
  ) {
    $this->currentUser       = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->ounitEntity       = $ounit_entity;
    $this->stringTranslation = $string_translation;

    $this->definition = $this->entityTypeManager
      ->getDefinition(self::ENTITY_TYPE);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('ewp_ounits_get.entity'),
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
      self::HEI_TYPE => $this->entityTypeManager
        ->getDefinition(self::HEI_TYPE)
        ->getLabel(),
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
        self::HEI_TYPE => ['data' => $this->ounitEntity
          ->heiLabel($provider->heiId())],
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
   * Helper method that provides a link for a provider's JSON:API resource list.
   */
  private function providerLoadLink(OunitProviderInterface $ounit_provider) {
    $text = $this->t('Load data');
    $rel = 'import-form';
    $options = ['attributes' => ['class' => ['button', 'button--primary']]];

    return $ounit_provider->toLink($text, $rel, $options);
  }

  /**
   * Helper method that provides a link for a provider's edit form.
   */
  private function providerEditLink(OunitProviderInterface $ounit_provider) {
    $admin_permission = $this->definition->getAdminPermission();

    if (!$this->currentUser->hasPermission($admin_permission)) {
      return '';
    }

    $text = $this->t('Edit provider');
    $rel = 'edit-form';
    $options = ['attributes' => ['class' => ['button']]];

    return $ounit_provider->toLink($text, $rel, $options);
  }

}
