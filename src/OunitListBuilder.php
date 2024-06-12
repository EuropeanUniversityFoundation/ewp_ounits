<?php

namespace Drupal\ewp_ounits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Organizational Unit entities.
 *
 * @ingroup ewp_ounits
 */
class OunitListBuilder extends EntityListBuilder {

  const PARENT_HEI = 'parent_hei';
  const PARENT_OUNIT = 'parent_ounit';

  const UNDEFINED = 'This field is not defined.';

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Organizational Unit ID');
    $header['label'] = $this->t('Label');
    $header[self::PARENT_OUNIT] = $this->t('Parent Organizational Unit');
    $header[self::PARENT_HEI] = $this->t('Parent Institution');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\ewp_ounits\Entity\Ounit $entity */
    $row['id'] = $entity->id();
    $row['label'] = $entity->toLink();
    /** @disregard P1013 */
    if ($entity->hasField(self::PARENT_OUNIT)) {
      /** @disregard P1013 */
      $parent_ounit = $entity->get(self::PARENT_OUNIT)->referencedEntities();
      $row[self::PARENT_OUNIT] = (!empty($parent_ounit))
        ? $parent_ounit[0]->toLink()
        : '';
    }
    else {
      $row[self::PARENT_OUNIT] = $this->t('%u', ['%u' => self::UNDEFINED]);
    }
    if ($entity->hasField(self::PARENT_HEI)) {
      /** @disregard P1013 */
      $parent_hei = $entity->get(self::PARENT_HEI)->referencedEntities();
      $row[self::PARENT_HEI] = (!empty($parent_hei))
        ? $parent_hei[0]->toLink()
        : '';
    }
    else {
      $row[self::PARENT_HEI] = $this->t('%u', ['%u' => self::UNDEFINED]);
    }
    return $row + parent::buildRow($entity);
  }

}
