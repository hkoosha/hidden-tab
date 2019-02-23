<?php

namespace Drupal\hidden_tab\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailerInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MailerSender {

  /**
   * Used by findMailerEntityById().
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mailerStorage;

  /**
   * To get current IP, for per ip accounting.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              RequestStack $request_stack) {
    $this->mailerStorage = $entity_type_manager->getStorage('hidden_tab_mailer');
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Find entity by id.
   *
   * @param $id
   *   Id of entity
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface
   *   Loaded entity if any.
   */
  private function findMailerEntityById($id): HiddenTabMailerInterface {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $mailer */
    $mailer = $this->mailerStorage->load($id);
    return $mailer;
  }

  // -------------------------------------------------------------- FIND CREDIT

  /**
   * Find mailer entity by params.
   *
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface|null $page
   *   The hidden tab page in question.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The entity in question.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function he(?HiddenTabPageInterface $page,
                     ?EntityInterface $entity,
                     ?AccountInterface $account): array {
    if ($page === NULL && $entity === NULL && $account === NULL) {
      throw new \RuntimeException('illegal state');
    }
    $q = $this->mailerStorage
      ->getQuery()
      ->condition('status', TRUE, '=');

    if (!$page) {
      $q->condition('target_hidden_tab_page', NULL, 'IS NULL');
    }
    else {
      $q->condition('target_hidden_tab_page', $page->id(), '=');
    }

    if (!$entity) {
      $q->condition('target_entity', NULL, 'IS NULL');
    }
    else {
      $q->condition('target_entity', $entity->id(), '=');
    }

    if (!$account) {
      $q->condition('target_user', NULL, 'IS NULL');
    }
    else {
      $q->condition('target_user', $account->id(), '=');
    }

    $ret = [];
    foreach ($q->execute() as $id) {
      $ret[] = $this::findMailerEntityById($id);
    }
    return $ret;
  }

  /**
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page in question.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The account in question.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function peu(HiddenTabPageInterface $page,
                      EntityInterface $entity,
                      AccountInterface $account): array {
    return $this::he($page, $entity, $account);
  }

  /**
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page in question.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity in question.
   * @param bool $account
   *   Dummy.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function pex(HiddenTabPageInterface $page,
                      EntityInterface $entity,
                      bool $account): array {
    if ($account) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he($page, $entity, NULL);
  }

  /**
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page in question.
   * @param bool $entity
   *   Dummy.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function pxu(HiddenTabPageInterface $page,
                      bool $entity,
                      AccountInterface $account): array {
    if ($entity) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he($page, NULL, $account);
  }

  /**
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page in question.
   * @param bool $entity
   *   Dummy.
   * @param bool $account
   *   Dummy.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function pxx(?HiddenTabPageInterface $page,
                      bool $entity,
                      bool $account): array {
    if ($entity || $account) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he($page, NULL, NULL);
  }

  /**
   * @param bool $page
   *   Dummy.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity in question.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function xeu(bool $page,
                      EntityInterface $entity,
                      AccountInterface $account): array {
    if ($page) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he(NULL, $entity, $account);
  }

  /**
   * @param bool $page
   *   Dummy.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity in question.
   * @param bool $account
   *   Dummy.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function xex(bool $page,
                      EntityInterface $entity,
                      bool $account): array {
    if ($page || $account) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he(NULL, $entity, NULL);
  }

  /**
   * @param bool $page
   *   Dummy.
   * @param bool $entity
   *   Dummy.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Loaded entities
   */
  public function xxu(bool $page,
                      bool $entity,
                      AccountInterface $account): array {
    if ($page || $entity) {
      throw new \RuntimeException('illegal state');
    }
    return $this::he(NULL, NULL, $account);
  }

  /**
   * Execute the mailer.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $mailer
   *   The mailer to send.
   */
  public function send(?EntityInterface $mailer) {
  }

}
