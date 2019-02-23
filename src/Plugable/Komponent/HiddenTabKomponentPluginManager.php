<?php

namespace Drupal\hidden_tab\Plugable\Komponent;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabKomponentAnon;
use Drupal\hidden_tab\Plugable\PluginManTrait;

/**
 * The plugin manager.
 *
 * @see \Drupal\hidden_tab\Plugable\Komponent\HiddenTabKomponentInterface
 */
class HiddenTabKomponentPluginManager extends DefaultPluginManager {

  use PluginManTrait;

  static $id = 'hidden_tab_komponent';

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/HiddenTabKomponent',
      $namespaces,
      $module_handler,
      HiddenTabKomponentInterface::class,
      HiddenTabKomponentAnon::class
    );
    $this->alterInfo('hidden_tab_komponent_info');
    $this->setCacheBackend($cache_backend, 'hidden_tab_komponent_plugin');
  }

  /**
   * All the komponents a plugin provides.
   *
   * @param string|null $id
   *   Id of the plugin in question.
   *
   * @return array
   *   All the komponents a plugin provides.
   *
   * @throws \drupal\component\plugin\exception\pluginnotfoundexception
   *
   * @see \Drupal\hidden_tab\Plugable\Komponent\HiddenTabKomponentInterface::komponents()
   */
  public static function komponentsOfPlugin(string $id): array {
    /** @noinspection PhpUndefinedMethodInspection */
    return static::man()->plugin($id)->komponents();
  }

  /**
   * Finds label of a plugin.
   *
   * @param string $plugin_id
   *   Plugin id.
   *
   * @return string
   *   Label of plugin.
   */
  public static function labelOfPlugin(string $plugin_id) {
    try {
      if (static::man()->exists($plugin_id)) {
        return static::man()->plugin($plugin_id)->label();
      }
      else {
        return t('Komponent missing: [' . $plugin_id . ']');
      }
    }
    catch (PluginNotFoundException $e) {
      throw new \LogicException('', 0, $e);
    }
  }

}
