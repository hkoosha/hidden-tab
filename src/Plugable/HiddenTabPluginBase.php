<?php
/**
 * Created by PhpStorm.
 * User: milan
 * Date: 1/31/19
 * Time: 10:45 AM
 */

namespace Drupal\hidden_tab\Plugable;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for all plugins of this module.
 */
abstract class HiddenTabPluginBase extends PluginBase implements HiddenTabPluginInterfaceBase {

  /**
   * See \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::id().
   */
  protected static $PID;

  /**
   * See \Drupal\hidden_tab\Plugable\HiddenTabPageInterface::label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPageInterface::label().
   */
  protected static $HTPLabel;

  /**
   * See \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface.
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPageInterface::description().
   */
  protected static $HTPDescription;

  /**
   * See \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::weight().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::weight().
   */
  protected static $HTPWeight;

  /**
   * See display().
   *
   * @var bool
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::display().
   */
  protected static $HTPDisplay;

  /**
   * See tags().
   *
   * @var array
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginBaseInterface::tags().
   */
  protected static $HTPTags;

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return static::$PID;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return static::$HTPLabel;
  }

  /**
   * {@inheritdoc}
   */
  public function description(): string {
    return static::$HTPDescription;
  }

  /**
   * {@inheritdoc}
   */
  public function weight(): int {
    return static::$HTPWeight;
  }

  /**
   * {@inheritdoc}
   */
  public function display(): bool {
    return static::$HTPDisplay;
  }

  /**
   * {@inheritdoc}
   */
  public function tags(): array {
    return isset(static::$HTPTags) ? static::$HTPTags : [];
  }

  /**
   * {@inheritdoc}
   */
  public function handleConfigForm(array &$form, ?FormStateInterface $form_state, $config) {

  }

  /**
   * {@inheritdoc}
   */
  public function handleConfigFormValidate(array &$form, FormStateInterface $form_state, $config) {

  }

  /**
   * {@inheritdoc}
   */
  public function handleConfigFormSubmit(?array &$form, FormStateInterface $form_state, $config) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_id);
  }

}
