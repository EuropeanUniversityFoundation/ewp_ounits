<?php

declare(strict_types=1);

namespace Drupal\ewp_ounits\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Organizational Unit entity.
 *
 * @ingroup ewp_ounits
 *
 * @ContentEntityType(
 *   id = "ounit",
 *   label = @Translation("Organizational Unit"),
 *   label_collection = @Translation("Organizational Unit"),
 *   label_singular = @Translation("Organizational Unit"),
 *   label_plural = @Translation("Organizational Units"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Organizational Unit",
 *     plural = "@count Organizational Units",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\ewp_ounits\OunitListBuilder",
 *     "views_data" = "Drupal\ewp_ounits\Entity\OunitViewsData",
 *     "access" = "Drupal\ewp_ounits\OunitAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ewp_ounits\Form\OunitForm",
 *       "edit" = "Drupal\ewp_ounits\Form\OunitForm",
 *       "delete" = "Drupal\ewp_ounits\Form\OunitDeleteForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ewp_ounits\OunitHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "ounit",
 *   revision_table = "ounit_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer organizational unit entities",
 *   translatable = FALSE,
 *   revisionable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *     "owner" = "uid"
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/ewp/ounit/list",
 *     "canonical" = "/ewp/ounit/{ounit}",
 *     "add-form" = "/ewp/ounit/add",
 *     "edit-form" = "/ewp/ounit/{ounit}/edit",
 *     "delete-form" = "/ewp/ounit/{ounit}/delete",
 *     "revision" = "/ewp/ounit/{ounit}/revisions/{ounit_revision}/view",
 *     "revision-delete-form" = "/ewp/ounit/{ounit}/revisions/{ounit_revision}/delete",
 *     "revision-revert-form" = "/ewp/ounit/{ounit}/revisions/{ounit_revision}/revert",
 *     "version-history" = "/ewp/ounit/{ounit}/revisions",
 *   },
 *   field_ui_base_route = "ounit.settings",
 *   common_reference_target = TRUE,
 *   constraints = {
 *     "ParentOunitInstitution" = {},
 *     "UniqueOunitCodePerInstitution" = {},
 *     "UniqueOunitIdPerInstitution" = {},
 *   }
 * )
 */
class Ounit extends RevisionableContentEntityBase implements OunitInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;
  use RevisionLogEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

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
   * Returns Anonymous as default owner.
   */
  public static function getDefaultEntityOwner() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Label'))
      ->setDescription(new TranslatableMarkup('The internal label of the Organizational Unit entity.'))
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
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    /** @var Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields['status']
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 20,
      ])
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the entity was last edited.'))
      ->setRevisionable(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setLabel(new TranslatableMarkup('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
