<?php

namespace Drupal\hidden_tab\Entity\Helper;

/**
 * Helper for \Drupal\hidden_tab\Entity\Base\PluginSupportingEntityInterface.
 *
 * @see \Drupal\hidden_tab\Entity\Base\MultiAspectPluginSupportingInterface
 */
trait MultiAspectPluginSupportingTrait {

  /**
   * Json encoded array of plugins configurations.
   *
   * @var string
   */
  protected $plugins;

  /**
   * {@inheritdoc}
   */
  public function delPlugin(string $plugin) {
    $all = $this->pluginConfigurations();
    unset($all[$plugin]);
    $this->set('plugins', json_encode($all));
  }

  /**
   * {@inheritdoc}
   */
  public function plugins(): array {
    return array_keys($this->pluginConfigurations());
  }

  /**
   * {@inheritdoc}
   */
  public function pluginConfiguration(string $plugin_id) {
    $configurations = $this->pluginConfigurations();
    return isset($configurations[$plugin_id]) ? $configurations[$plugin_id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginConfiguration(string $plugin_id, $configuration) {
    $configurations = $this->pluginConfigurations();
    $configurations[$plugin_id] = $configuration;
    $this->set('plugins', json_encode($configurations));
  }

  /**
   * {@inheritdoc}
   */
  public function pluginConfigurations(): array {
    // Shouldn't really happen.
    if ((is_array($this->plugins) && count($this->plugins) == 0) && !$this->plugins) {
      \Drupal::logger('hidden_tab')
        ->warning("entity's plugin configuration array is not set, creating it entity-type={type} entity={entity}", [
          'entity-type' => $this->getEntityTypeId(),
          'entity' => $this->id(),
        ]);
      $this->plugins = json_encode([]);
    }
    return json_decode($this->plugins, TRUE);
  }

}
