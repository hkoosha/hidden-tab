<?php
/**
 * Created by PhpStorm.
 * User: milan
 * Date: 1/31/19
 * Time: 10:34 AM
 */

namespace Drupal\hidden_tab\Plugable;

/**
 * Helper for plugin managers.
 */
trait PluginManTrait {

  /**
   * Helper goody stuff for plugins.
   *
   * @var \Drupal\hidden_tab\Plugable\PluginHelper
   */
  static $manager;

  /**
   * The helper for this plugin manager.
   *
   * @return \Drupal\hidden_tab\Plugable\PluginHelper
   *   The helper for this plugin manager.
   */
  public static function man(): PluginHelper {
    if (!isset(static::$manager)) {
      if (!static::$id) {
        throw new \RuntimeException('illegal state');
      }
      static::$manager = new PluginHelper(static::$id);
    }
    return static::$manager;
  }

}