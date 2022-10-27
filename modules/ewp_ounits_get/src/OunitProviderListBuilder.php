<?php

namespace Drupal\ewp_ounits_get;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Organizational Unit providers.
 */
class OunitProviderListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['hei_id'] = $this->t('Institution ID');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\ewp_ounits_get\OunitProviderInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['hei_id'] = $entity->heiId();
    $row['status'] = $entity->status()
      ? $this->t('Enabled')
      : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity) + [
      'preview' => [
        'title' => $this->t('Preview'),
        'weight' => 0,
        'url' => $entity->toUrl('preview-form'),
      ],
      'import' => [
        'title' => $this->t('Import data'),
        'weight' => 1,
        'url' => $entity->toUrl('import-form'),
      ],
    ];

    return $operations;
  }

}
