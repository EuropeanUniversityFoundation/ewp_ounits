<?php

namespace Drupal\ewp_ounits_get\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Organizational Unit provider form.
 *
 * @property \Drupal\ewp_ounits_get\OunitProviderInterface $entity
 */
class OunitProviderForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Label for the Organizational Unit provider.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ewp_ounits_get\Entity\OunitProvider::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['api_params'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API parameters'),
      '#tree' => FALSE,
    ];

    $form['api_params']['hei_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Institution ID'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->get('hei_id'),
      '#description' => $this->t('Format: %format', [
        '%format' => 'domain.tld',
      ]),
      '#required' => TRUE,
    ];

    $form['api_params']['collection_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource collection URL'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->get('collection_url'),
      '#description' => $this->t('The URL containing the %ounit collection.', [
        '%ounit' => $this->t('Organizational Unit'),
      ]) . '<br />' . $this->t('Format: %format', [
        '%format' => 'https://domain.tld/jsonapi/ounit',
      ]),
      '#required' => TRUE,
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->get('description'),
      '#description' => $this->t('Description (optional).'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = [
      '@entity_type' => $this->t('Organizational Unit provider'),
      '%label' => $this->entity->label(),
    ];
    $message = $result == SAVED_NEW
      ? $this->t('Created new @entity_type %label.', $message_args)
      : $this->t('Updated @entity_type %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
