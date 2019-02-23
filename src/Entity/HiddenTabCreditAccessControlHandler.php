<?php

namespace Drupal\hidden_tab\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the hidden tab credit entity type.
 */
class HiddenTabCreditAccessControlHandler extends EntityAccessControlHandler {

  const PERMISSION_ADMINISTER = 'administer hidden tab credit';

  const OP_ADMINISTER = self::PERMISSION_ADMINISTER;

  const PERMISSION_UPDATE = 'update hidden tab credit';

  const PERMISSION_DELETE = 'delete hidden tab credit';

  const PERMISSION_CREATE = 'create hidden tab credit';

  const PERMISSION_VIEW = 'view hidden tab credit';

  const OP_UPDATE = 'update';

  const OP_DELETE = 'delete';

  const OP_VIEW = 'view';

  const SIMPLE_OP_PERM = [
    self::OP_UPDATE => self::PERMISSION_UPDATE,
    self::OP_DELETE => self::PERMISSION_DELETE,
    self::OP_VIEW => self::PERMISSION_VIEW,
  ];

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    $admin = AccessResult::allowedIfHasPermission($account, self::PERMISSION_ADMINISTER);
    if ($admin->isAllowed()) {
      return $admin;
    }
    if (!isset(self::SIMPLE_OP_PERM[$operation])) {
      return AccessResult::forbidden('unsupported operation');
    }
    return AccessResult::allowedIfHasPermissions($account, [
      static::PERMISSION_ADMINISTER,
      self::SIMPLE_OP_PERM[$operation],
    ], 'OR');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, [
      static::PERMISSION_ADMINISTER,
      static::PERMISSION_CREATE,
    ], 'OR');
  }

}
