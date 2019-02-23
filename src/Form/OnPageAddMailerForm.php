<?php
/**
 * Created by PhpStorm.
 * User: milan
 * Date: 2/3/19
 * Time: 2:27 PM
 */

namespace Drupal\hidden_tab\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailer;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Form\Base\OnPageAdd;

/**
 * To add mailer directly on tab page.
 *
 * @property \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $entity
 *
 * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface
 * @see \Drupal\hidden_tab\Controller\XPageRenderController
 */
class OnPageAddMailerForm extends OnPageAdd {

  /**
   * {@inheritdoc}
   */
  protected $ID = 'hidden_tab_on_page_add_mailer_form';

  /**
   * {@inheritdoc}
   */
  protected $prefix = 'hidden_tab_on_page_add_mailer_form_';

  /**
   * {@inheritdoc}
   */
  protected $label = 'Mailer';

  /**
   * {@inheritdoc}
   */
  protected static $type = 'hidden_tab_mailer';

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    HiddenTabMailer::validateForm(
      $form_state,
      $this->prefix,
      TRUE,
      'node',
      NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getValues(FormStateInterface $form_state): array {
    return [
      'target_hidden_tab_page' => $form_state->getValue($this->prefix . 'target_hidden_tab_page'),
      'target_user' => $form_state->getValue($this->prefix . 'target_user'),
      'target_entity' => $form_state->getValue($this->prefix . 'target_entity'),
      'target_entity_type' => $form_state->getValue($this->prefix . 'target_entity_type'),
      'target_entity_bundle' => $form_state->getValue($this->prefix . 'target_entity_bundle'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormElements(EntityInterface $target_entity, HiddenTabPageInterface $page): array {
    $form = HiddenTabMailer::littleForm(NULL, $this->prefix, TRUE);
    foreach ([
               'target_entity',
               'target_entity_type',
               'target_entity_bundle',
             ] as $item) {
      unset($form[$this->prefix . $item]);
    }
    return $form;
  }

}