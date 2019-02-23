<?php

namespace Drupal\hidden_tab\Event;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The event published when a new Hidden Tab Page is being created.
 *
 * So that other modules may add their own elements to the form. Has 3 phase
 * of form creation, validation and save.
 */
class HiddenTabPageFormEvent extends Event {

  const EVENT_NAME = 'HIDDEN_TAB_PAGE_FORM_EVENT';

  /**
   * If the event is for when the edit/add form is being created.
   */
  const PHASE_FORM = 0;

  /**
   * If the event is for when the edit/add form is being validated.
   */
  const PHASE_VALIDATE = 1;

  /**
   * If the event is for when the edit/add form is being saved.
   */
  const PHASE_SUBMIT = 2;

  /**
   * The generated form for entity creation/edit.
   *
   * @var array
   */
  public $form;

  /**
   * The form state.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  public $formState;

  /**
   * From the constants in the class, which phase this event belongs to.
   *
   * That is, form creation, validation and save.
   *
   * @var int
   *
   * @see \Drupal\hidden_tab\Event\HiddenTabPageFormEvent::PHASE_FORM
   * @see \Drupal\hidden_tab\Event\HiddenTabPageFormEvent::PHASE_VALIDATE
   * @see \Drupal\hidden_tab\Event\HiddenTabPageFormEvent::PHASE_SUBMIT
   */
  public $phase;

  /**
   * The page entity being created (might not have been saved yet).
   *
   * @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface
   */
  public $page;

  /**
   * HiddenTabPageFormEvent constructor.
   *
   * @param \Drupal\Component\Uuid\Php $uuid
   *   Handy service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Handy service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $credit_storage
   *   Handy service.
   * @param array $form
   *   The form being generated for the entity creation/edit.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The entity being created/edited. Might not have been saved yet.
   * @param int $phase
   *   The phase (creation, validation, save) this event belongs to. See the
   *   class constants.
   * @param string $current_uri
   *   Here, to redirect to.
   */
  public function __construct(array &$form,
                              FormStateInterface $form_state,
                              HiddenTabPageInterface $page,
                              int $phase) {
    $this->form = &$form;
    $this->formState = $form_state;
    $this->phase = $phase;
    $this->page = $page;
  }

  public function isEdit() {
    return !$this->page->isNew();
  }

  public function has(string $prefix, string $name) {
    return $this->formState->hasValue($prefix . $name);
  }

  /**
   * Get a single value from form state or all of them.
   *
   * @param string $prefix
   *   String prefixed to $name, so that does not collide with other form
   *   values.
   * @param string|NULL $name
   *   Name of the value to get or NULL if all values is desired.
   *
   * @return array|mixed
   *   Value in form state, or all values if name is not given.
   */
  public function get(string $prefix = NULL, string $name = NULL) {
    if ($name === NULL) {
      assert($prefix === NULL);
      return $this->formState->getValues();
    }
    elseif ($name) {
      assert($prefix !== NULL);
    }
    return $this->formState->getValue($prefix . $name);
  }

  /**
   * Set value on form state.
   *
   * @param string $prefix
   *   String prefixed to $name, so that does not collide with other form
   *   values.
   * @param string $name
   *   Name of the value.
   * @param $value
   *   The actual value.
   *
   * @return \Drupal\hidden_tab\Event\HiddenTabPageFormEvent
   *   This.
   */
  public function set(string $prefix, string $name, $value): HiddenTabPageFormEvent {
    $this->formState->setValue($prefix . $name, $value);
    return $this;
  }

  public function error(string $message, string $prefix = '', string $name = '') {
    $this->formState->setErrorByName($prefix . $name, $message);
  }

}
