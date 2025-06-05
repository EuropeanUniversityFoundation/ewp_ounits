<?php

namespace Drupal\ewp_ounits\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Organizational Unit entity.
 *
 * @ingroup ewp_ounits
 *
 * @ContentEntityType(
 *   id = "ounit",
 *   label = @Translation("Organizational Unit"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ewp_ounits\OunitListBuilder",
 *     "views_data" = "Drupal\ewp_ounits\Entity\OunitViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ewp_ounits\Form\OunitForm",
 *       "add" = "Drupal\ewp_ounits\Form\OunitForm",
 *       "edit" = "Drupal\ewp_ounits\Form\OunitForm",
 *       "delete" = "Drupal\ewp_ounits\Form\OunitDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ewp_ounits\OunitHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\ewp_ounits\OunitAccessControlHandler",
 *   },
 *   base_table = "ounit",
 *   translatable = FALSE,
 *   admin_permission = "administer organizational unit entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/ewp/ounit/{ounit}",
 *     "add-form" = "/ewp/ounit/add",
 *     "edit-form" = "/ewp/ounit/{ounit}/edit",
 *     "delete-form" = "/ewp/ounit/{ounit}/delete",
 *     "collection" = "/admin/ewp/ounit/list",
 *   },
 *   field_ui_base_route = "ounit.settings",
 *   common_reference_target = TRUE,
 * )
 */
class Ounit extends ContentEntityBase implements OunitInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('label')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('label', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The internal label of the Organizational Unit entity.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -20,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 20,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
