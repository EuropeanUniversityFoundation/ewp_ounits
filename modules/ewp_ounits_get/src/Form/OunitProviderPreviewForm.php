<?php

namespace Drupal\ewp_ounits_get\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\ewp_ounits_get\JsonDataProcessor;
use Drupal\ewp_ounits_get\OunitEntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Organizational Unit provider preview form.
 *
 * @property \Drupal\ewp_ounits_get\OunitProviderInterface $entity
 */
class OunitProviderPreviewForm extends EntityForm {

  const JSONAPI_RESOURCE_TYPE = 'ounit';
  const JSONAPI_OUNIT_ID = 'ounitId';
  const JSONAPI_OUNIT_CODE = 'ounitCode';

  const HEI_ID = 'hei_id';
  const COLLECTION_URL = 'collection_url';

  /**
   * Corresponding Institution entity.
   *
   * @var string
   */
  protected $heiExists;

  /**
   * SharedTempStore key.
   *
   * @var string
   */
  protected $tempStoreKey;

  /**
   * JSON:API endpoint.
   *
   * @var string
   */
  protected $endpoint;

  /**
   * Organizational Unit data.
   *
   * @var array
   */
  protected $ounitData;

  /**
   * The Organizational Unit entity manager.
   *
   * @var \Drupal\ewp_ounits_get\OunitEntityManagerInterface
   */
  protected $ounitEntity;

  /**
   * JSON data fetcher.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataFetcherInterface
   */
  protected $jsonDataFetcher;

  /**
   * JSON data processor.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataProcessorInterface
   */
  protected $jsonDataProcessor;

  /**
   * JSON data validation service.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataSchemaValidatorInterface
   */
  protected $jsonDataValidator;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->ounitEntity          = $container->get('ewp_ounits_get.entity');
    $instance->jsonDataFetcher      = $container->get('ewp_ounits_get.fetch');
    $instance->jsonDataProcessor    = $container->get('ewp_ounits_get.json');
    $instance->jsonDataValidator    = $container->get('ewp_ounits_get.validate.ounit.occapi');
    $instance->loggerFactory        = $container->get('logger.factory');
    $instance->logger = $instance->loggerFactory->get('ewp_ounits_get');
    $instance->messenger            = $container->get('messenger');
    return $instance;
  }

  /**
   * Set data for persistency.
   */
  public function setData() {
    if (!isset($this->heiExists)) {
      $this->heiExists = $this->ounitEntity
        ->heiIdExists($this->entity->heiId());
    }

    if (!isset($this->tempStoreKey)) {
      $this->tempStoreKey = implode('.', [
        self::JSONAPI_RESOURCE_TYPE,
        $this->entity->heiId()
      ]);
    }

    if (!isset($this->endpoint)) {
      $this->endpoint = $this->entity->get(self::COLLECTION_URL);
    }

    if (!isset($this->ounitData)) {
      $json_data = $this->jsonDataFetcher
        ->load($this->tempStoreKey, $this->endpoint);

      $collection = \json_decode($json_data, TRUE);

      $this->ounitData = $collection[JsonDataProcessor::DATA_KEY] ?? [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $this->setData();

    $form = parent::form($form, $form_state);

    $form['summary'] = [
      '#type' => 'details',
      '#title' => $this->t('Summary'),
      '#open' => TRUE,
      '#attributes' => [
        'id' => 'previewFormSummary',
      ],
      '#weight' => 0,
    ];

    $form['summary'][self::HEI_ID] = [
      '#type' => 'item',
      '#title' => $this->t('Institution'),
      '#markup' => $this->ounitEntity->heiLabel($this->entity->heiId()),
    ];

    $form['summary'][self::COLLECTION_URL] = [
      '#type' => 'item',
      '#title' => $this->t('Resource collection URL'),
      '#markup' => '<code>' . $this->endpoint . '</code>',
    ];

    $form['summary']['count'] = [
      '#type' => 'item',
      '#title' => $this->t('Item count'),
      '#markup' => '<code>' . count($this->ounitData) . '</code>',
    ];

    $header = $this->buildTableHeader();

    $rows = [];

    foreach ($this->ounitData as $resource) {
      $rows[] = $this->buildTableRow($resource);
    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No data to display.'),
      '#weight' => 2,
    ];

    if (count($rows) > 10) {
      $form['skip_to_end'] = [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->t('Skip to end'),
        '#attributes' => [
          'href' => '#previewFormEnd',
        ],
        '#weight' => 1,
      ];

      $form['back_to_top'] = [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->t('Back to top'),
        '#attributes' => [
          'id' => 'previewFormEnd',
          'href' => '#previewFormSummary',
        ],
        '#weight' => 3,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = [];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    return $this->entity;
  }

  /**
   * Build the table header.
   */
  public function buildTableHeader() {
    return [
      JsonDataProcessor::TYPE_KEY,
      JsonDataProcessor::ID_KEY,
      JsonDataProcessor::TITLE_KEY,
      self::JSONAPI_OUNIT_ID,
      self::JSONAPI_OUNIT_CODE,
      $this->t('Errors'),
      JsonDataProcessor::LINKS_KEY,
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

    if (!empty($errors)) {
      foreach ($errors as $error) {
        $this->messenger->addError($error);
      }
    }

    $uri = $this->jsonDataProcessor
      ->getResourceLinkByType($data, JsonDataProcessor::SELF_KEY);

    $url_options = ['attributes' => ['target' => '_blank']];

    $row = [
      $this->jsonDataProcessor->getResourceType($data),
      $this->jsonDataProcessor->getResourceId($data),
      $this->jsonDataProcessor->getResourceTitle($data),
      $attributes[self::JSONAPI_OUNIT_ID],
      $attributes[self::JSONAPI_OUNIT_CODE],
      count($errors),
      Link::fromTextAndUrl(
        JsonDataProcessor::SELF_KEY,
        Url::fromUri($uri, $url_options)
      ),
    ];

    return $row;
  }

}
