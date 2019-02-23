<?php

namespace Drupal\hidden_tab\Plugable\MailDiscovery;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabMailDiscoveryAnon;
use Drupal\hidden_tab\Plugable\PluginManTrait;

/**
 * HiddenTabMailDiscovery plugin manager.
 *
 * @see \Drupal\hidden_tab\Plugable\MailDiscovery\HiddenTabMailDiscoveryInterface
 */
class HiddenTabMailDiscoveryPluginManager extends DefaultPluginManager {

  use PluginManTrait;

  static $id = 'hidden_tab_mail_discovery';

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/HiddenTabMailDiscovery',
      $namespaces,
      $module_handler,
      HiddenTabMailDiscoveryInterface::class,
      HiddenTabMailDiscoveryAnon::class
    );
    $this->alterInfo('hidden_tab_mail_discovery_info');
    $this->setCacheBackend($cache_backend, 'hidden_tab_mail_discovery_plugin');
  }

}
