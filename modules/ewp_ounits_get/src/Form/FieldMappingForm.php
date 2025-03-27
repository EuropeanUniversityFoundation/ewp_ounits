<?php

namespace Drupal\ewp_ounits_get\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ewp_ounits_get\OunitFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure EWP OUnits GET field mapping for this site.
 */
class FieldMappingForm extends ConfigFormBase {

  /**
   * The Organizational Unit field manager.
   *
   * @var \Drupal\ewp_ounits_get\OunitFieldManagerInterface
   */
  protected $ounitFields;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\ewp_ounits_get\OunitFieldManagerInterface $ounit_field_manager
   *   The Organizational Unit field manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typedConfigManager,
    OunitFieldManagerInterface $ounit_field_manager
  ) {
    parent::__construct($config_factory, $typedConfigManager);
    $this->ounitFields = $ounit_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('ewp_ounits_get.fields'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ewp_ounits_get_field_mapping';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ewp_ounits_get.fieldmap'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ewp_ounits_get.fieldmap');
    $fieldmap = $config->get('field_mapping');
    $defaults = [];

    foreach ($fieldmap as $field => $attribute) {
      $field_keys = explode('__', $field);
      $attribute_option = str_replace('__', '.', $attribute);

      $defaults[$field_keys[0]][$field_keys[1]] = $attribute_option;
    }

    $entity_fields = $this->ounitFields->getEntityFields();
    $options = $this->ounitFields->getAttributeOptions();

    foreach ($entity_fields as $field => $info) {
      $form[$field] = [
        '#type' => 'details',
        '#title' => $info['label'],
        '#required' => $info['required'],
        '#open' => $info['required'],
        '#tree' => TRUE,
      ];

      foreach ($info['properties'] as $property => $value) {
        $title = $this->t('@attribute for the %property property', [
          '@attribute' => $this->t('JSON:API attribute'),
          '%property' => $property,
        ]);

        $description = $this->t('Data type should be %type.', [
          '%type' => $value,
        ]);

        $form[$field][$property] = [
          '#type' => 'select',
          '#title' => $title,
          '#options' => $options,
          '#description' => $description,
          '#empty_value' => '',
          '#required' => $info['required'],
          '#default_value' => $defaults[$field][$property] ?? NULL,
        ];

        if (!$info['required']) {
          $empty_option = '- ' . $this->t('No mapping') . ' -';
          $form[$field][$property]['#empty_option'] = $empty_option;
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fieldmap = [];
    $fields = array_keys($this->ounitFields->getEntityFields());
    $values = $form_state->getValues();

    foreach ($fields as $field) {
      foreach ($values[$field] as $property => $value) {
        if (!empty($value)) {
          $fieldmap[$field . '__' . $property] = str_replace('.', '__', $value);
        }
      }
    }

    $config = $this->config('ewp_ounits_get.fieldmap');
    $config->set('field_mapping', $fieldmap);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
