<?php

namespace Drupal\ewp_ounits_get\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Organizational Unit provider preview form.
 *
 * @property \Drupal\ewp_ounits_get\OunitProviderInterface $entity
 */
class OunitProviderImportForm extends OunitProviderPreviewForm {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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
    $ounit_id_attribute = $this->jsonDataProcessor
      ->getResourceAttribute($data, self::JSONAPI_OUNIT_ID);
    $ounit_id = $ounit_id_attribute[self::JSONAPI_OUNIT_ID];

    $ounit_id_exists = $this->ounitEntity
      ->ounitIdExists($this->entity->heiId(), $ounit_id) ?? [];

    if (!empty($ounit_id_exists)) {
      foreach ($ounit_id_exists as $entity) {
        $title = $entity->toLink();
      }
    }
    else {
      /** @disregard P1013 */
      $title = $this->jsonDataProcessor->getResourceTitle($data);
    }

    $ounit_code_attribute = $this->jsonDataProcessor
      ->getResourceAttribute($data, self::JSONAPI_OUNIT_CODE);
    $ounit_code = $ounit_code_attribute[self::JSONAPI_OUNIT_CODE];

    $errors = $this->jsonDataValidator->validateSchema($data);

    $hei_id_exists = $this->ounitEntity
      ->heiIdExists($this->entity->heiId());

    if (empty($hei_id_exists)) {
      $errors[] = $this->t('Missing Institution.');
    }

    $row = [
      $title,
      $ounit_id,
      $ounit_code,
      (empty($errors))
        ? $this->buildOperations($ounit_id_exists, $ounit_id)
        : $this->t('@count error(s) found.', ['@count' => count($errors)]),
    ];

    return $row;
  }

  /**
   * Build operations for a table row.
   */
  public function buildOperations(array $ounit_id_exists, string $ounit_id) {
    if (!empty($ounit_id_exists)) {
      return $this->t('Nothing to do here.');
    }

    $text = $this->t('Import data');
    $route = 'entity.ounit.import.ounit_id';
    $params = [
      $this->entity->getEntityTypeId() => $this->entity->id(),
      OunitEntityManagerInterface::FIELD_ID => $ounit_id,
    ];
    $options = ['attributes' => ['class' => ['button', 'button--primary']]];

    return Link::createFromRoute($text, $route, $params, $options);
  }

}
