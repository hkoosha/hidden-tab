<?php

namespace Drupal\hidden_tab\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailer;
use Drupal\hidden_tab\Form\Base\EntityFormBase;

/**
 * Form controller for the hidden tab mailer entity edit forms.
 */
class HiddenTabMailerForm extends EntityFormBase {

  protected $targetEntityType = 'node';

  protected $prefix = '';

  protected $type = 'hidden_tab_mailer';

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    HiddenTabMailer::validateForm(
      $form_state,
      $this->prefix,
      'node',
      TRUE,
      $this->getEntity()->id()
    );
    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity0() {
  }

}
