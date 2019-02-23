<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabKomponent;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Entity\HiddenTabPlacementInterface;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabKomponentAnon;
use Drupal\hidden_tab\Plugable\Komponent\HiddenTabKomponentPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the hidden_tab_komponent.
 *
 * Makes views and their display available as komponents.
 *
 * @HiddenTabKomponentAnon(
 *   id = "hidden_tab_views_komponent",
 *   type = "views"
 * )
 */
class HiddenTabViewsKomponent extends HiddenTabKomponentPluginBase {

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::id()
   */
  protected static $PID = 'hidden_tab_views_komponent';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::label()
   */
  protected static $HTPLabel = 'Views';

  /**
   * See description().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::description()
   */
  protected static $HTPDescription = 'TODO';

  /**
   * See weight().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::weight()
   */
  protected static $HTPWeight = 0;

  /**
   * See tags().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::tags()
   */
  protected static $HTPTags = [];

  /**
   * Type of komponent this plugins represents.
   *
   * @var string
   */
  protected $komponentType = 'views';

  /**
   * To load all tne views.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $viewsStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              string $plugin_id,
                              $plugin_definition,
                              EntityStorageInterface $views_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->komponentTypeLabel = t('Views');
    $this->viewsStorage = $views_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('view')
    );
  }

  /**
   * All the views and their displays as available komponents by this plugin.
   *
   * @return array
   *   All the views and their displays as the available komponents by this
   *   plugin.
   */
  public function komponents(): array {
    static $fast;
    if (!isset($fast)) {
      $fast['definitions'] = &drupal_static(__FUNCTION__);
      $definitions = [];
      foreach ($this->viewsStorage->loadMultiple() as $view) {
        /** @var  \Drupal\views\Entity\View $view */
        foreach ($view->get('display') as $disp) {
          $definitions[$view->id() . '::' . $disp['id']] =
            $view->label() . ' : ' . $disp['display_title'];
        }
      }
      ksort($definitions);
      $fast['definitions']['values'] = $definitions;
    }
    return $fast['definitions']['values'];
  }

  /**
   * {@inheritdoc}
   */
  public function render(EntityInterface $entity,
                         HiddenTabPageInterface $page,
                         HiddenTabPlacementInterface $placement) {
    list($view, $display) = explode('::', $placement->komponent());
    return views_embed_view($view, $display);
  }

}
