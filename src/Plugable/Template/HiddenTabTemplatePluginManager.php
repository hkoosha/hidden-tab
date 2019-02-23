<?php

namespace Drupal\hidden_tab\Plugable\Template;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabTemplateAnon;
use Drupal\hidden_tab\Plugable\PluginManTrait;

/**
 * The plugin manager.
 *
 * @see \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplateInterface
 */
class HiddenTabTemplatePluginManager extends DefaultPluginManager {

  use PluginManTrait;

  static $id = 'hidden_tab_template';

  /**
   * Constructs HiddenTabTemplatePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/HiddenTabTemplate',
      $namespaces,
      $module_handler,
      HiddenTabTemplateInterface::class,
      HiddenTabTemplateAnon::class
    );
    $this->alterInfo('hidden_tab_template_info');
    $this->setCacheBackend($cache_backend, 'hidden_tab_template');
  }

  /**
   * Caption to image uri array, label of templates and their preview images.
   *
   * @return array
   *   label to image uri array, label of templates and their preview images.
   */
  public static function templatePreviewImages(): array {
    $options = [];
    /** @var \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplateInterface $plugin */
    foreach (static::man()->plugins() as $plugin) {
      $options[$plugin->label()] = $plugin->imageUri();
    }
    return $options;
  }

  /**
   * Id to label array of regions in the template provided by the plugin.
   *
   * @param string $plugin_id
   *   Id of the plugin providing the template.
   *
   * @return array
   *   Id to label array of regions available in the template.
   */
  public static function regionsOfTemplate(string $plugin_id): array {
    /** @var \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplateInterface $plugin */
    $plugin = static::man()->plugin($plugin_id);
    return $plugin->regions();
  }

}
