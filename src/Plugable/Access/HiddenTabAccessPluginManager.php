<?php

namespace Drupal\hidden_tab\Plugable\Access;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabAccessAnon;
use Drupal\hidden_tab\Plugable\PluginManTrait;

/**
 * The plugin manager.
 *
 * @see \Drupal\hidden_tab\Plugable\Access\HiddenTabAccessInterface
 */
class HiddenTabAccessPluginManager extends DefaultPluginManager {

  use PluginManTrait;

  static $id = 'hidden_tab_access';

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/HiddenTabAccess',
      $namespaces,
      $module_handler,
      HiddenTabAccessInterface::class,
      HiddenTabAccessAnon::class
    );
    $this->alterInfo('hidden_tab_access_info');
    $this->setCacheBackend($cache_backend, 'hidden_tab_access_plugin');
  }

}
