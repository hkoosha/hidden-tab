<?php

namespace Drupal\hidden_tab\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\hidden_tab\Utility;

/**
 * Makes Hidden Tab Pages (Secret Uris) available as tabs on nodes.
 */
class DynamicLocalTasks extends DeriverBase {

  const CURRENT_SUPPORTED_BASE_ROUTE = 'entity.node.canonical';

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach (Utility::pages() as $page) {
      $link = $page->id();
      $id = "hidden_tab.tab_$link";
      $this->derivatives[$id] = $base_plugin_definition;
      $this->derivatives[$id]['title'] = $page->label();
      $this->derivatives[$id]['route_name'] = "hidden_tab.tab_$link";
      $this->derivatives[$id]['enabled'] = 1;
      $this->derivatives[$id]['base_route'] = self::CURRENT_SUPPORTED_BASE_ROUTE;
    }
    return $this->derivatives;
  }

}
