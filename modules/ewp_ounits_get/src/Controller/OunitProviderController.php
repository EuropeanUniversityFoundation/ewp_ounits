<?php

namespace Drupal\ewp_ounits_get\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides additional title callbacks for Organizational Unit providers.
 */
class OunitProviderController extends ControllerBase {

  /**
   * The _title_callback for the Organizational Unit provider edit form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The edit form title.
   */
  public function editFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Edit %ounit_provider Organizational Unit provider', [
      '%ounit_provider' => $ounit_provider->label()
    ]);
  }

  /**
   * The _title_callback for the Organizational Unit provider preview form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The preview form title.
   */
  public function previewFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Preview %ounit_provider Organizational Unit provider', [
      '%ounit_provider' => $ounit_provider->label()
    ]);
  }

  /**
   * The _title_callback for the Organizational Unit provider import form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ounit_provider
   *   The current Organizational Unit provider.
   *
   * @return string
   *   The import form title.
   */
  public function importFormTitle(EntityInterface $ounit_provider) {
    return $this->t('Import Organizational Units from %ounit_provider.', [
      '%ounit_provider' => $ounit_provider->label(),
    ]);
  }

}
