<?php

namespace Drupal\ewp_ounits\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Organizational Unit edit forms.
 *
 * @ingroup ewp_ounits
 */
class OunitForm extends ContentEntityForm {

  const OUNIT_ID = 'ounit_id';

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\ewp_ounits\Entity\Ounit $entity */
    $form = parent::buildForm($form, $form_state);

    // Prepare changes to the ounit_id widget.
    $ounit_id_value = $form[self::OUNIT_ID]['widget'][0]['value'];
    // Remove the requirement from the form element.
    $ounit_id_value['#required'] = FALSE;
    // Set a placeholder indicating the UUID fallback.
    $placeholder = $this->t('Leave empty to use UUID as OUnit ID');
    $ounit_id_value['#attributes']['placeholder'] = $placeholder;
    // Apply the changes to the widget.
    $form[self::OUNIT_ID]['widget'][0]['value'] = $ounit_id_value;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue(self::OUNIT_ID)[0]['value'])) {
      $ounit_id_value = $this->entity->uuid->value;
      $form_state->setValue([self::OUNIT_ID , 0 , 'value'], $ounit_id_value);
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()
          ->addMessage($this->t('Created the %label Organizational Unit.', [
            '%label' => $entity->label(),
          ]));
        break;

      default:
        $this->messenger()
          ->addMessage($this->t('Saved the %label Organizational Unit.', [
            '%label' => $entity->label(),
          ]));
    }
    $form_state->setRedirect('entity.ounit.canonical', [
      'ounit' => $entity->id()
    ]);
  }

}
