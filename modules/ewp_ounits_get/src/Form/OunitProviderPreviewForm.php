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
class OunitProviderPreviewForm extends EntityForm {

  /**
   * JSON data fetcher service.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataFetcherInterface
   */
  protected $jsonDataFetcher;

  /**
   * JSON data processing service.
   *
   * @var \Drupal\ewp_ounits_get\JsonDataProcessorInterface
   */
  protected $jsonDataProcessor;

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
    $instance->jsonDataFetcher      = $container->get('ewp_ounits_get.fetch');
    $instance->jsonDataProcessor    = $container->get('ewp_ounits_get.json');
    $instance->loggerFactory        = $container->get('logger.factory');
    $instance->logger = $instance->loggerFactory->get('ewp_ounits_get');
    $instance->messenger            = $container->get('messenger');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $temp_store_key = $this->entity->heiId() . '.ounit';
    $endpoint = $this->entity->get('collection_url');

    $json_data = $this->jsonDataFetcher->load($temp_store_key, $endpoint);
    $collection = \json_decode($json_data, TRUE);
    $data = $collection[JsonDataProcessor::DATA_KEY];

    $url_options = ['attributes' => ['target' => '_blank']];
    $endpoint_url = Url::fromUri($endpoint, $url_options);
    $endpoint_link = Link::fromTextAndUrl($endpoint, $endpoint_url);

    $form['header'] = [
      '#type' => 'details',
      '#title' => $this->t('Summary'),
      '#open' => TRUE,
      '#attributes' => [
        'id' => 'previewFormHeader',
      ],
      '#weight' => 0,
    ];

    $form['header']['hei_id'] = [
      '#type' => 'item',
      '#title' => $this->t('Institution ID'),
      '#markup' => '<code>' . $this->entity->heiId() . '</code>',
    ];

    $form['header']['collection_url'] = [
      '#type' => 'item',
      '#title' => $this->t('Resource collection URL'),
      '#markup' => '<code>' . $endpoint_link->toString() . '</code>',
    ];

    $form['header']['count'] = [
      '#type' => 'item',
      '#title' => $this->t('Item count'),
      '#markup' => '<code>' . count($data) . '</code>',
    ];

    $header = [
      JsonDataProcessor::TYPE_KEY,
      JsonDataProcessor::ID_KEY,
      JsonDataProcessor::TITLE_KEY,
      JsonDataProcessor::LINKS_KEY
    ];

    $rows = [];

    foreach ($data as $resource) {
      $uri = $this->jsonDataProcessor
        ->getResourceLinkByType($resource, JsonDataProcessor::SELF_KEY);

      $row = [
        $this->jsonDataProcessor->getResourceType($resource),
        $this->jsonDataProcessor->getResourceId($resource),
        $this->jsonDataProcessor->getResourceTitle($resource),
        Link::fromTextAndUrl(
          JsonDataProcessor::SELF_KEY,
          Url::fromUri($uri, $url_options)
        ),
      ];

      $rows[] = $row;
    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
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
          'href' => '#previewFormHeader',
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

}
