<?php

namespace Drupal\ewp_ounits_get\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\ewp_ounits_get\JsonDataProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Organizational Unit provider preview form.
 *
 * @property \Drupal\ewp_ounits_get\OunitProviderInterface $entity
 */
class OunitProviderImportForm extends OunitProviderPreviewForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    dpm($this);

    return $form;
  }

  /**
   * Build the table header.
   */
  public function buildTableHeader() {
    return [
      $this->t('Title'),
      $this->t('ID'),
      $this->t('Code'),
      $this->t('Operations'),
    ];
  }

  /**
   * Build a table row from a data array.
   */
  public function buildTableRow(array $data) {
    foreach ([self::JSONAPI_OUNIT_ID, self::JSONAPI_OUNIT_CODE] as $key) {
      $attributes[$key] = $this->jsonDataProcessor
        ->getResourceAttribute($data, $key)[$key];
    }

    $errors = $this->jsonDataValidator->validateSchema($data);

    $row = [
      $this->jsonDataProcessor->getResourceTitle($data),
      $attributes[self::JSONAPI_OUNIT_ID],
      $attributes[self::JSONAPI_OUNIT_CODE],
      (empty($this->jsonDataValidator->validateSchema($data)))
        ? $this->t('Good')
        : $this->t('Bad'),
    ];

    return $row;
  }

}
