<?php

namespace Drupal\hidden_tab\EventSubscriber;

use Drupal\Component\Uuid\Php;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailer;
use Drupal\hidden_tab\Event\HiddenTabPageFormEvent;
use Drupal\hidden_tab\Service\MailerSender;

/**
 * To list all mailer entities of a page, on it's edit form.
 */
class HiddenTabPageFormSubscriberForNewMailer extends ForNewEntityFormBase {

  /**
   * {@inherit}
   */
  protected $prefix = 'hidden_tab_add_new_mailer_subscriber_0__';

  /**
   * {@inherit}
   */
  protected $currentlyTargetEntity = 'node';

  /**
   * {@inherit}
   */
  protected $e_type = 'hidden_tab_mailer';

  /**
   * {@inherit}
   */
  protected $label;

  /**
   * To find the editing entity's entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mailerStorage;

  /**
   * To query for existing entities on validate.
   *
   * @var \Drupal\hidden_tab\Service\MailerSender
   */
  protected $mailService;

  /**
   * {@inheritdoc}
   */
  public function __construct(TranslationInterface $t,
                              MessengerInterface $messenger,
                              EntityTypeManagerInterface $entity_type_manager,
                              MailerSender $mailer_sender) {
    parent::__construct($t, $messenger, $entity_type_manager);
    $this->mailerStorage = $entity_type_manager->getStorage('hidden_tab_mailer');
    $this->mailService = $mailer_sender;
    $this->label = t('Mailer');
  }

  /**
   * {@inheritdoc}
   */
  protected function addForm(HiddenTabPageFormEvent $event): array {
    return HiddenTabMailer::littleForm(
      $event->formState, $this->prefix, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function onValidate0(HiddenTabPageFormEvent $event) {
    HiddenTabMailer::validateForm($event->formState,
      $this->prefix,
      'node',
      TRUE,
      NULL);
  }

  /**
   * {@inheritdoc}
   */
  protected function onSave0(HiddenTabPageFormEvent $event): array {
    return HiddenTabMailer::extractFormValues($event->formState, $this->prefix, TRUE);
  }

}
