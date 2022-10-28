<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Organizational Unit entity manager.
 */
class OunitEntityManager implements OunitEntityManagerInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Organizational Unit field manager.
   *
   * @var \Drupal\ewp_ounits_get\OunitFieldManagerInterface
   */
  protected $ounitFields;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\ewp_ounits_get\OunitFieldManagerInterface $ounit_field_manager
   *   The Organizational Unit field manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    OunitFieldManagerInterface $ounit_field_manager,
    TranslationInterface $string_translation
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->ounitFields       = $ounit_field_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Creates an Organizational Unit ID within an Institution.
   *
   * @param string $hei_id
   *   The Institution ID.
   * @param array $ounit_data
   *   The Organizational Unit data.
   *
   * @return array|null
   *   The created Organizational Unit, or NULL if the Institution is missing.
   */
  public function create(string $hei_id, array $ounit_data): ?array {
    $parent_hei = $this->entityTypeManager->heiIdExists($hei_id);

    if (empty($parent_hei)) { return NULL; }

    $new = $this->entityTypeManager
      ->getStorage(self::ENTITY_TYPE)
      ->create($ounit_data);
    $new->save();

    $ounit = $this->ounitIdExists($hei_id, $ounit_data[self::FIELD_ID]);

    return $ounit;
  }

  /**
   * Checks the existence of an Institution.
   *
   * @param string $hei_id
   *   The Institution ID.
   *
   * @return array
   *   The matching Institution.
   */
  public function heiIdExists(string $hei_id): array {
    $hei = $this->entityTypeManager
      ->getStorage(self::REFERENCED_TYPE)
      ->loadByProperties([self::UNIQUE_FIELD => $hei_id]);

    return $hei;
  }

  /**
   * Provides an Institution label, dependending on whether the entity exists.
   *
   * @param string $hei_id
   *   The Institution ID.
   *
   * @return string
   *   The Institution label.
   */
  public function heiLabel(string $hei_id): string {
    $hei_exists = $this->heiIdExists($hei_id);

    foreach ($hei_exists as $id => $hei) {
      $hei_label = $hei->toLink()->toString();
    }

    return $hei_label ?? $this->t('Institution ID: %hei_id', [
      '%hei_id' => $hei_id
    ]);
  }

  /**
   * Checks the existence of an Organizational Unit ID within an Institution.
   *
   * @param string $hei_id
   *   The Institution ID.
   * @param string $ounit_id
   *   The Organizational Unit ID.
   *
   * @return array|null
   *   The matching Organizational Unit, or NULL if the Institution is missing.
   */
  public function ounitIdExists(string $hei_id, string $ounit_id): ?array {
    $parent_hei = $this->heiIdExists($hei_id);

    if (empty($parent_hei)) { return NULL; }

    $ounit = $this->entityTypeManager
      ->getStorage(self::ENTITY_TYPE)
      ->loadByProperties([
        self::FIELD_ID => $ounit_id,
        self::ENTITY_REFERENCE => array_keys($parent_hei)[0],
      ]);

    return $ounit;
  }

  /**
   * Checks the existence of an Organizational Unit code within an Institution.
   *
   * @param string $hei_id
   *   The Institution ID.
   * @param string $ounit_code
   *   The Organizational Unit code.
   *
   * @return array|null
   *   The matching Organizational Unit, or NULL if the Institution is missing.
   */
  public function ounitCodeExists(string $hei_id, string $ounit_code): ?array {
    $parent_hei = $this->heiIdExists($hei_id);

    if (empty($parent_hei)) { return NULL; }

    $ounit = $this->entityTypeManager
      ->getStorage(self::ENTITY_TYPE)
      ->loadByProperties([
        self::FIELD_CODE => $ounit_code,
        self::ENTITY_REFERENCE => array_keys($parent_hei)[0],
      ]);

    return $ounit;
  }

}
