<?php

namespace Drupal\ewp_ounits_import\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ewp_ounits_import\OunitImporterInterface;

/**
 * Defines the organizational unit importer entity type.
 *
 * @ConfigEntityType(
 *   id = "ounit_importer",
 *   label = @Translation("Organizational Unit importer"),
 *   label_collection = @Translation("Organizational Unit importers"),
 *   label_singular = @Translation("organizational unit importer"),
 *   label_plural = @Translation("organizational unit importers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count organizational unit importer",
 *     plural = "@count organizational unit importers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\ewp_ounits_import\OunitImporterListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ewp_ounits_import\Form\OunitImporterForm",
 *       "edit" = "Drupal\ewp_ounits_import\Form\OunitImporterForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "ounit_importer",
 *   admin_permission = "administer ounit_importer",
 *   links = {
 *     "collection" = "/admin/structure/ounit-importer",
 *     "add-form" = "/admin/structure/ounit-importer/add",
 *     "edit-form" = "/admin/structure/ounit-importer/{ounit_importer}",
 *     "delete-form" = "/admin/structure/ounit-importer/{ounit_importer}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class OunitImporter extends ConfigEntityBase implements OunitImporterInterface {

  /**
   * The organizational unit importer ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The organizational unit importer label.
   *
   * @var string
   */
  protected $label;

  /**
   * The organizational unit importer status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The ounit_importer description.
   *
   * @var string
   */
  protected $description;

}
