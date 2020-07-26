<?php

namespace Drupal\ewp_ounits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Organizational Unit entities.
 *
 * @ingroup ewp_ounits
 */
class OunitListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Organizational Unit ID');
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\ewp_ounits\Entity\Ounit $entity */
    $row['id'] = $entity->id();
    $row['label'] = Link::createFromRoute(
      $entity->label(),
      'entity.ounit.edit_form',
      ['ounit' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
