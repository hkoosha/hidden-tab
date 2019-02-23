<?php

namespace Drupal\hidden_tab\Entity\Base;

use Drupal\Core\Entity\EntityInterface;

/**
 * Many Hidden Tab entity types have plugins and their corresponding config.
 *
 * This is the common interface for those entity types.
 *
 * As each entity may need multiple type of plugins, to distinguish between
 * them without IDs colliding, they are given aspects (plugin type).
 */
interface MultiAspectPluginSupportingInterface extends EntityInterface {

  /**
   * Plugins managing the entity's aspects.
   *
   * @param string $aspect
   *   What type of plugin is in question.
   *
   * @return array
   *   Array of the corresponding plugin IDs
   */
  public function plugins(string $aspect): array;

  /**
   * Plugin configuration, json_decoded.
   *
   * @param string $aspect
   *   What type of plugin is in question.
   * @param string $plugin_id
   *   Id of the plugin in question.
   *
   * @return mixed|null
   *   Plugin configuration managing the main aspect of entity (json decoded).
   */
  public function pluginConfiguration(string $aspect, string $plugin_id);

  /**
   * Corresponding setter of pluginConfiguration().
   *
   * @param string $aspect
   *   What type of plugin is in question.
   * @param string $plugin_id
   *   Id of the plugin in question.
   * @param $configuration
   *   The configuration data, will be json_encoded.
   */
  public function setPluginConfiguration(string $aspect, string $plugin_id, $configuration);

  /**
   * All plugin configurations keyed by their ID.
   *
   * @param string $aspect
   *   What type of plugin is in question.
   *
   * @return array
   *   All plugin configurations (each json_decoded).
   */
  public function pluginConfigurations(string $aspect): array;

}
