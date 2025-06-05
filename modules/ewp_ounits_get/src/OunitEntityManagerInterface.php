<?php

namespace Drupal\ewp_ounits_get;

/**
 * Defines an interface for an Organizational Unit entity manager.
 */
interface OunitEntityManagerInterface {

  const ENTITY_TYPE      = 'ounit';
  const FIELD_ID         = 'ounit_id';
  const FIELD_CODE       = 'ounit_code';
  const ENTITY_REFERENCE = 'parent_hei';
  const REFERENCED_TYPE  = 'hei';
  const UNIQUE_FIELD     = 'hei_id';

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
  public function create(string $hei_id, array $ounit_data);

  /**
   * Checks the existence of the parent Institution.
   *
   * @param string $hei_id
   *   The Institution ID.
   *
   * @return array
   *   The matching Institution.
   */
  public function heiIdExists(string $hei_id);

  /**
   * Provides an Institution label, dependending on whether the entity exists.
   *
   * @param string $hei_id
   *   The Institution ID.
   *
   * @return array
   *   The Institution label as a render array.
   */
  public function heiLabel(string $hei_id);

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
  public function ounitIdExists(string $hei_id, string $ounit_id);

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
  public function ounitCodeExists(string $hei_id, string $ounit_code);

}
