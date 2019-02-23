<?php

namespace Drupal\hidden_tab\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Entity\HiddenTabCreditInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CreditCharging {

  const BYPASS_CHARGING_PERMISSION = 'bypass credit charging';

  private const CHECK_ORDER_REPLACE = [
    ' ',
    '-',
    '/',
    '\\',
    '-',
    '+',
    '=',
    '|',
    '"',
    "'",
    ".",
    ";",
    "&",
    "*",
  ];

  const INVALID_SAFETY_NET = [-1, -2];

  const INFINITE = -3;

  const MIN = 0;

  /**
   * Used by findCreditEntityById().
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $creditStorage;

  /**
   * To get current IP, for per ip accounting.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              RequestStack $request_stack) {
    $this->creditStorage = $entity_type_manager->getStorage('hidden_tab_credit');
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Find entity by id.
   *
   * @param $id
   *   Id of entity
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface
   *   Loaded entity if any.
   */
  private function findCreditEntityById($id): HiddenTabCreditInterface {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabCreditInterface $credit */
    $credit = $this->creditStorage->load($id);
    return $credit;
  }

  // -------------------------------------------------------------- FIND CREDIT

  /**
   * Find credit entity by params.
   *
   * TODO do not take full entity, get ID.
   *
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface|null $page
   *   The hidden tab page in question.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The entity in question.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The account in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
   *   Loaded entities
   */
  public function he(?HiddenTabPageInterface $page,
                     ?EntityInterface $entity,
                     ?AccountInterface $account): array {
    if ($page === NULL && $entity === NULL && $account === NULL) {
      throw new \RuntimeException('illegal state');
    }
    $q = $this->creditStorage
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
      $ret[] = $this::findCreditEntityById($id);
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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
   * @return \Drupal\hidden_tab\Entity\HiddenTabCreditInterface[]
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

  // ----------------------------------------------------------------- CHARGING

  /**
   * Check credit, charge credit and return TRUE meaning account had credit.
   *
   * @param \Drupal\hidden_tab\Entity\HiddenTabCreditInterface $credit
   *   The credit to check credit of.
   * @param \Drupal\Core\Session\AccountInterface $from_user
   *   The user who is going to be charged credit.
   *
   * @return bool
   *   True if account has credit.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function charge(HiddenTabCreditInterface $credit,
                         AccountInterface $from_user): bool {
    // TODO may also reset span?
    if ($from_user->hasPermission(self::BYPASS_CHARGING_PERMISSION)) {
      return TRUE;
    }

    if ($credit->credit() < -2) {
      return TRUE;
    }

    $key = $credit->isPerIp() ? $this->request->getClientIp() : 'everyone';
    $cfg = NULL;

    if ($credit->creditSpan() > 0) {
      $cfg = $credit->ipAccounting(CreditCharging::class) ?: [];
      $last = isset($cfg[$key]) ? $cfg[$key] : PHP_INT_MAX - 99999999999;
      if (\time() < ($last + $credit->creditSpan())) {
        return TRUE;
      }
    }

    if ($credit->credit() === 0) {
      if ($from_user->hasPermission('administer site configuration')) {
        \Drupal::messenger()->addWarning('administrative view');
        return TRUE;
      }
      return FALSE;
    }

    if ($from_user->hasPermission('administer site configuration')) {
      \Drupal::messenger()->addWarning('administrative view');
      return TRUE;
    }

    $credit->set('credit', $credit->credit() - 1);
    if ($credit->creditSpan() > 0) {
      $cfg[$key] = \time();
      $credit->setIpAccounting(CreditCharging::class, $cfg);
    }
    $credit->save();
    return TRUE;
  }

  /**
   * Check to see if credit value is in valid range.
   *
   * @param int|string $credit
   *   Amount of credit.
   *
   * @return bool
   *   True if credit is valid.
   */
  public function isValid($credit): bool {
    if ((((int) $credit) . '') !== ($credit . '')) {
      return FALSE;
    }
    $credit = (int) $credit;
    return !in_array($credit, static::invalidValues())
      && $credit >= static::minValid();
  }

  /**
   * Check to see if credit span value is in valid range.
   *
   * @param int|string $credit_span
   *   Amount of credit_span.
   *
   * @return bool
   *   True if credit span is valid.
   */
  public function isSpanValid($credit_span): bool {
    if ((((int) $credit_span) . '') !== ($credit_span . '')) {
      return FALSE;
    }
    return ((int) $credit_span) >= 0;
  }

  /**
   * Check to see if credit value is denoting infinite credit.
   *
   * @param int $credit
   *   Amount of credit.
   *
   * @throws \Exception
   *   In case credit is not in valid range.
   *
   * @return bool
   */
  public function isInfinite(int $credit): bool {
    if (!$this->isValid($credit)) {
      throw new \RuntimeException('illegal state');
    }
    return $credit === static::INFINITE;
  }

  /**
   * Set invalid values which credit can not (MUST not) be.
   *
   * @return array
   *   Set of invalid values.
   */
  public static function invalidValues(): array {
    return static::INVALID_SAFETY_NET;
  }

  /**
   * Minimum valid value credit can get.
   *
   * @return int
   *   Minimum valid value credit can get.
   */
  public static function minValid(): int {
    return static::INFINITE;
  }

  /**
   * @param string $to_fix
   *
   * @return string[]
   */
  public static function fixCreditCheckOrder(string $to_fix): array {
    $with = ',';
    $double_with = ',,';

    foreach (static::CHECK_ORDER_REPLACE as $r) {
      $to_fix = str_replace($r, $with, $to_fix);
    }
    while (strpos($to_fix, $double_with) !== FALSE) {
      $to_fix = str_replace($double_with, $with, $to_fix);
    }
    $fix = explode($with, $to_fix);
    $ok = [
      'peu',
      'pex',
      'pxu',
      'pxx',
      'xeu',
      'xex',
      'xxu',
    ];
    foreach ($fix as $order) {
      if (!in_array($order, $ok, TRUE)) {
        \Drupal::logger('hidden_tab')
          ->error('bad access order value order={order}', [
            'order' => $to_fix,
          ]);
        return [];
      }
    }
    return $fix;
  }

}