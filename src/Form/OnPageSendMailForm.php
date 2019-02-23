<?php

namespace Drupal\hidden_tab\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailerInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Service\MailerSender;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * To add mailer directly on tab page.
 *
 * @property \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $entity
 *
 * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface
 * @see \Drupal\hidden_tab\Controller\XPageRenderController
 */
class OnPageSendMailForm extends FormBase {

  const ID = 'hidden_tab_on_page_send_mail_form';

  /**
   * To find related mailers.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mailerStorage;

  /**
   * To send
   *
   * @var \Drupal\hidden_tab\Service\MailerSender
   */
  protected $mailer;


  /**
   * {@inheritdoc}
   */
  public function __construct(EntityStorageInterface $mailer_storage,
                              MailerSender $sender) {
    $this->mailerStorage = $mailer_storage;
    $this->mailer = $sender;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('hidden_tab_mailer'),
      $container->get('hidden_tab.mail_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return self::ID;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form,
                            FormStateInterface $form_state,
                            EntityInterface $entity = NULL,
                            HiddenTabPageInterface $page = NULL) {
    if ($page === NULL || $page === NULL) {
      throw new \LogicException('illegal state, page or target entity not given');
    }

    $form['page'] = [
      '#type' => 'value',
      '#value' => $page->id(),
    ];
    $form['entity'] = [
      '#type' => 'value',
      '#value' => $entity->id(),
    ];
    $form['entity_type'] = [
      '#type' => 'value',
      '#value' => $entity->getEntityTypeId(),
    ];
    $form['bundle'] = [
      '#type' => 'value',
      '#value' => $entity->bundle(),
    ];


    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $mailers = $this->mailerStorage->loadByProperties([
      'target_hidden_tab_page' => $page->id(),
    ]);
    $mailers = array_filter($mailers, function (HiddenTabMailerInterface $m) use ($entity, $page): bool {
      if ($m->targetEntityBundle() && $entity->bundle() !== $m->targetEntityBundle()) {
        return FALSE;
      }
      elseif ($m->targetEntityId() && $m->targetEntityId() !== $entity->id()) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    });
    foreach ($mailers as $mailer) {
      $form['actions'] = [
        '#type' => 'submit',
        '#value' => 'Send ' . $mailer->id(),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO check access.
    $page = $form_state->getValue('page');
    $entity = $form_state->getValue('entity');
    $bundle = $form_state->getValue('bundle');

    $mailers = $this->mailerStorage->loadByProperties([
      'target_hidden_tab_page' => $page,
    ]);
    $mailers = array_filter($mailers, function (HiddenTabMailerInterface $m) use ($entity, $page, $bundle): bool {
      if ($m->targetEntityBundle() && $bundle !== $m->targetEntityBundle()) {
        return FALSE;
      }
      elseif ($m->targetEntityId() && $m->targetEntityId() !== $entity) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    });

    $mailer = NULL;
    foreach ($mailers as $mailer) {
      if (('Send ' . $mailer->id()) === $form_state->getValue('op')) {
        break;
      }
    }
    if (!$mailer) {
      $this->messenger()->addError('Mailer not found');
    }
    else {
      $this->messenger()->addStatus('Mail sent');
      $this->mailer->send($mailer);
    }

    $form_state->disableRedirect();
  }

}
