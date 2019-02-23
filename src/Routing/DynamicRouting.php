<?php

namespace Drupal\hidden_tab\Routing;

use Drupal\hidden_tab\Controller\XPageRenderController;
use Drupal\hidden_tab\Utility;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class DynamicRoute. Adds route of Hidden Tabs (Secret Uris) to route system.
 *
 * @package Drupal\hidden_tab\Routing
 */
class DynamicRouting {

  /**
   * Creates dynamic route for all the Hidden Tab Page entities.
   *
   * Disabled pages ($page->isEnabled()) are skipped.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   All the routes created by this class.
   */
  public function routes(): RouteCollection {
    $op['parameters']['node']['type'] = 'entity:node';
    $op['no_cache'] = 'TRUE';
    $rc = new RouteCollection();
    foreach (Utility::pages() as $page) {
      if (!$page->status()) {
        continue;
      }
      $route = new Route(
        '/node/{node}/' . $page->tabUri(),
        [
          '_controller' => XPageRenderController::class . '::display',
          '_title' => $page->label(),
        ],
        $page->tabViewPermission()
          ? ['_permission' => $page->tabViewPermission()]
          : ['_access' => 'TRUE'],
        $op
      );
      $rc->add('hidden_tab.tab_' . $page->id(), $route);
      if ($page->secretUri()) {
        $route = new Route(
          '/node/{node}/' . $page->secretUri(),
          [
            '_controller' => XPageRenderController::class . '::display',
            '_title' => $page->label(),
          ],
          $page->secretUriViewPermission()
            ? ['_permission' => $page->secretUriViewPermission()]
            : ['_access' => 'TRUE'],
          $op
        );
        $rc->add('hidden_tab.uri_' . $page->id(), $route);
      }
    }
    return $rc;
  }

}
