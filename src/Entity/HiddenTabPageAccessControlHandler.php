<?php

namespace Drupal\hidden_tab\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Plugable\Access\HiddenTabAccessPluginManager;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Defines the access control handler for the hidden tab page entity type.
 */
class HiddenTabPageAccessControlHandler extends EntityAccessControlHandler {

  const PERMISSION_ADMINISTER = 'administer hidden tab page';

  const PERMISSION_VIEW_ALL_TABS = 'view all hidden tab pages';

  const PERMISSION_VIEW_ALL_URIS = 'view all hidden tab pages via uri';

  const PERMISSION_VIEW_SECRET_URI = 'view secret uri';

  const PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF = 'view hidden tab page on page admin stuff';

  const OP_ADMINISTER = self::PERMISSION_ADMINISTER;

  const OP_VIEW_ALL_TABS = self::PERMISSION_VIEW_ALL_TABS;

  const OP_VIEW_ALL_URIS = self::PERMISSION_VIEW_ALL_URIS;

  const OP_VIEW_SECRET_URI = self::PERMISSION_VIEW_SECRET_URI;

  const OP_VIEW_ON_PAGE_ADMIN_STUFF = self::PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF;

  // -------------------------------------------------------------------------

  const PERMISSION_VIEW = 'view hidden tab page';

  const PERMISSION_UPDATE = 'update hidden tab page';

  const PERMISSION_DELETE = 'delete hidden tab page';

  const PERMISSION_CREATE = 'create hidden tab page';

  const OP_VIEW = 'view';

  const OP_UPDATE = 'update';

  const OP_DELETE = 'delete';

  const SIMPLE_OP_PERM = [
    self::OP_UPDATE => self::PERMISSION_UPDATE,
    self::OP_DELETE => self::PERMISSION_DELETE,
  ];

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity,
                         $operation,
                         AccountInterface $account = NULL,
                         $return_as_object = FALSE,
                         EntityInterface $context_entity = NULL,
                         ParameterBag $bag = NULL) {
    $account = $this->prepareUser($account);
    $langcode = $entity->language()->getId();

    if ($operation === 'view label' && $this->viewLabelOperation == FALSE) {
      $operation = 'view';
    }

    $cid = $entity->uuid() ?: $entity->getEntityTypeId() . ':' . $entity->id();

    if (($return = $this->getCache($cid, $operation, $langcode, $account)) !== NULL) {
      return $return_as_object ? $return : $return->isAllowed();
    }

    $access = array_merge(
      $this->moduleHandler()->invokeAll('entity_access', [
        $entity,
        $operation,
        $account,
      ]),
      $this->moduleHandler()
        ->invokeAll($entity->getEntityTypeId() . '_access', [
          $entity,
          $operation,
          $account,
        ])
    );

    $return = $this->processAccessHookResults($access);

    if (!$return->isForbidden()) {
      $return = $return->orIf($this->checkAccess($entity, $operation, $account, $context_entity, $bag));
    }
    $result = $this->setCache($return, $cid, $operation, $langcode, $account);
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity,
                                 $operation,
                                 AccountInterface $account,
                                 EntityInterface $context_entity = NULL,
                                 ParameterBag $bag = NULL): AccessResult {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface $entity */
    /** @var \Drupal\hidden_tab\Plugable\Access\HiddenTabAccessInterface $plugin */

    // We do not want admin to see the page with wrong hash.
    $admin = AccessResult::allowedIfHasPermission($account, self::PERMISSION_ADMINISTER)
      ->andIf(AccessResult::allowedIf($operation !== self::OP_VIEW_SECRET_URI));
    if ($admin->isAllowed()) {
      if (!$entity->enable()) {
        // TODO add to them.
        \Drupal::messenger()
          ->addWarning(t('If you are visiting this entity, beware: this entity is not enabled'));
      }
      return $admin;
    }

    if (isset(self::SIMPLE_OP_PERM[$operation])) {
      return AccessResult::allowedIfHasPermission($account, self::SIMPLE_OP_PERM[$operation]);
    }

    if (!$entity->enable()) {
      return AccessResult::forbidden('not enabled');
    }

    switch ($operation) {
      case self::OP_VIEW:
        if (AccessResult::allowedIfHasPermission($account, self::PERMISSION_VIEW_ALL_TABS)
          ->isAllowed()) {
          return AccessResult::allowed();
        }
        if (!AccessResult::allowedIfHasPermission($account, self::PERMISSION_VIEW)
          ->isAllowed()) {
          return AccessResult::forbidden();
        }
        if ($entity->tabViewPermission() && !AccessResult::allowedIfHasPermission($account, $entity->tabViewPermission())
            ->isAllowed()) {
          return AccessResult::forbidden();
        }
        if ($entity->targetUserId() && $entity->targetUserId() !== $account->id()) {
          return AccessResult::forbidden();
        }
        if ($context_entity) {
          if ($entity->targetEntityType() && $context_entity->getEntityTypeId() !== $entity->targetEntityType()) {
            return AccessResult::forbidden();
          }
          if ($entity->targetEntityBundle() && $context_entity->bundle() !== $entity->bundle()) {
            return AccessResult::forbidden();
          }
          if ($entity->targetEntityId() && $context_entity->id() !== $entity->targetEntityId()) {
            return AccessResult::forbidden();
          }
        }
        return AccessResult::allowed();

      case self::PERMISSION_VIEW_SECRET_URI:
        $access = AccessResult::allowedIfHasPermissions($account, [
          self::PERMISSION_VIEW_SECRET_URI,
          $entity->secretUriViewPermission(),
        ]);
        if (!$access->isAllowed()) {
          return $access;
        }

        if ($context_entity === NULL || $bag === NULL) {
          return AccessResult::forbidden();
        }

        $access = AccessResult::allowed();
        foreach (HiddenTabAccessPluginManager::man()->plugins() as $plugin) {
          $can = $plugin->canAccess(
            $context_entity,
            $account,
            $entity,
            $bag,
            self::PERMISSION_VIEW_SECRET_URI
          );
          $access = $access->andIf($can);
        }
        return $access;

      case self::PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF:
        return AccessResult::allowedIfHasPermission($account,
          self::PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF);

      default:
        return AccessResult::forbidden();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, [
      self::PERMISSION_ADMINISTER,
      self::PERMISSION_CREATE,
    ], 'OR');
  }

}
