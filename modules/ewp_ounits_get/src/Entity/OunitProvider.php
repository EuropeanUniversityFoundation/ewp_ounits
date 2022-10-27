<?php

namespace Drupal\ewp_ounits_get\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ewp_ounits_get\OunitProviderInterface;

/**
 * Defines the Organizational Unit provider entity type.
 *
 * @ConfigEntityType(
 *   id = "ounit_provider",
 *   label = @Translation("Organizational Unit provider"),
 *   label_collection = @Translation("Organizational Unit providers"),
 *   label_singular = @Translation("Organizational Unit provider"),
 *   label_plural = @Translation("Organizational Unit providers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Organizational Unit provider",
 *     plural = "@count Organizational Unit providers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\ewp_ounits_get\OunitProviderListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ewp_ounits_get\Form\OunitProviderForm",
 *       "edit" = "Drupal\ewp_ounits_get\Form\OunitProviderForm",
 *       "preview" = "Drupal\ewp_ounits_get\Form\OunitProviderPreviewForm",
 *       "import" = "Drupal\ewp_ounits_get\Form\OunitProviderImportForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "ounit_provider",
 *   admin_permission = "administer ounit provider entities",
 *   links = {
 *     "collection" = "/admin/ewp/ounit/provider",
 *     "add-form" = "/admin/ewp/ounit/provider/add",
 *     "edit-form" = "/admin/ewp/ounit/provider/{ounit_provider}",
 *     "preview-form" = "/admin/ewp/ounit/provider/{ounit_provider}/preview",
 *     "import-form" = "/admin/ewp/ounit/provider/{ounit_provider}/import",
 *     "delete-form" = "/admin/ewp/ounit/provider/{ounit_provider}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "hei_id" = "hei_id"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "hei_id",
 *     "collection_url",
 *     "description"
 *   }
 * )
 */
class OunitProvider extends ConfigEntityBase implements OunitProviderInterface {

  /**
   * The Organizational Unit provider ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Organizational Unit provider label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Institution SCHAC code covered by the Organizational Unit provider.
   *
   * @var string
   */
  protected $hei_id;

  /**
   * The Organizational Unit provider collection URL.
   *
   * @var string
   */
  protected $collection_url;

  /**
   * The Organizational Unit provider status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The Organizational Unit provider description.
   *
   * @var string
   */
  protected $description;

  /**
   * Returns the Institution ID.
   *
   * @return string|null
   *   The Institution ID if it exists, or NULL otherwise.
   */
  public function heiId() {
    return $this->get('hei_id');
  }

}
