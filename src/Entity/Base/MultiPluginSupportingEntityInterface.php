<?php

namespace Drupal\hidden_tab\Entity\Base;

use Drupal\Core\Entity\EntityInterface;

/**
 * Many Hidden Tab entity types have plugins and their corresponding config.
 */
interface MultiPluginSupportingEntityInterface extends EntityInterface {

  /**
   * Del a plugin to the list of plugins managing the entity's plugable aspect.
   *
   * @param string $plugin
   *   The plugin in question.
   */
  public function delPlugin(string $plugin);

  /**
   * Plugins managing the entity's aspects.
   *
   * @return array
   *   Array of the corresponding plugin IDs
   */
  public function plugins(): array;

  /**
   * Plugin configuration, json_decoded.
   *
   * @param string $plugin_id
   *   Id of the plugin in question.
   *
   * @return mixed|null
   *   Plugin configuration managing the main aspect of entity (json decoded).
   */
  public function pluginConfiguration(string $plugin_id);

  /**
   * Corresponding setter of pluginConfiguration().
   *
   * @param string $plugin_id
   *   Id of the plugin in question.
   * @param $configuration
   *   The configuration data, will be json_encoded.
   */
  public function setPluginConfiguration(string $plugin_id, $configuration);

  /**
   * All plugin configurations keyed by their ID.
   *
   * @return array
   *   All plugin configurations (each json_decoded).
   */
  public function pluginConfigurations(): array;

}
