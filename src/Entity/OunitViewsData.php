<?php

namespace Drupal\ewp_ounits\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Organizational Unit entities.
 */
class OunitViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
