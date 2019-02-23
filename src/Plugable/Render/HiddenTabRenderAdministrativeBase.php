<?php

namespace Drupal\hidden_tab\Plugable\Render;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageAccessControlHandler;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;

abstract class HiddenTabRenderAdministrativeBase extends HiddenTabRenderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity,
                         HiddenTabPageInterface $page,
                         AccountInterface $user): AccessResult {
    $perm = HiddenTabPageAccessControlHandler::PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF;
    return $page->access($perm, $user)
      ? AccessResult::allowed()
      : AccessResult::forbidden();
  }

}

