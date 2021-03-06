<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabRender;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabRenderAnon;
use Drupal\hidden_tab\Plugable\Render\HiddenTabRenderAdministrativeBase;
use Drupal\hidden_tab\Plugable\Render\HiddenTabRenderSafeTrait;
use Drupal\hidden_tab\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Displays an administrative view of a hidden tab page's mailers.
 *
 * @HiddenTabRenderAnon(
 *   id = "hidden_tab_admin_mailers_list"
 * )
 */
class HiddenTabRenderMailersList extends HiddenTabRenderAdministrativeBase {

  use HiddenTabRenderSafeTrait;

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::id()
   */
  protected static $PID = 'hidden_tab_admin_mailers_list';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::label()
   */
  protected static $HTPLabel = 'Admin Mailers';

  /**
   * See description().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::description()
   */
  protected static $HTPDescription = "Displays an administrative view of a hidden tab page's mailers";

  /**
   * See weight().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::weight()
   */
  protected static $HTPWeight = 7;

  /**
   * See tags().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::tags()
   */
  protected static $HTPTags = [];

  /**
   * To load mailers.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              string $plugin_id,
                              $plugin_definition,
                              EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
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
      $container->get('entity_type.manager')
        ->getStorage('hidden_tab_mailer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render0(EntityInterface $entity,
                          HiddenTabPageInterface $page,
                          AccountInterface $user,
                          ParameterBag $bag,
                          array &$output) {
    $add_link['@add'] = Url::fromRoute('entity.hidden_tab_mailer.add_form', [
      'page' => $page->id(),
      'target-entity' => $entity->id(),
      'target-entity-type' => $entity->getEntityTypeId(),
      'target-entity-bundle' => $entity->bundle(),
      'lredirect' => Utility::redirectHere(),
    ])->toString();

    $table = [
      '#type' => 'table',
      '#caption' => $this->t('Mailers, <a href="@add">Add a new one</a>.', $add_link),
      '#header' => [
        $this->t('ID'),
        $this->t('Status'),
        $this->t('Schedule'),
        $this->t('Upcoming'),
        $this->t('User'),
        $this->t('Entity'),
        $this->t('Operations'),
      ],
      '#empty' => t('There are no items yet, <a href="@add">Add a new one</a>.', $add_link),
    ];

    /** @var \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[] $entities */
    $entities = $this->storage->loadByProperties([
      'target_hidden_tab_page' => $page->id(),
    ]);
    $df = \Drupal::service('date.formatter');
    foreach ($entities as $entity) {
      // TODO move to generic
      // TODO try catch
      $v['id'] = [
        '#markup' => $entity->id(),
      ];
      $v['status'] = [
        '#markup' => Utility::mark($entity->isEnabled()),
      ];
      $v['schedule'] = [
        '#markup' => $entity->emailSchedule() ?: Utility::CROSS,
      ];
      $v['upcoming'] = [
        '#markup' => $entity->nextSchedule() ? $df->format($entity->nextSchedule()) : Utility::CROSS,
      ];
      $v['user'] = [
        '#markup' => $entity->targetUserEntity()
          ? $entity->targetUserEntity()->label()
          : Utility::CROSS,
      ];
      $v['entity'] = [
        '#markup' => $entity->targetEntity()
          ? $entity->targetEntity()->label()
          : Utility::CROSS,
      ];
      $v['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];
      $v['operations']['#links']['remove'] = [
        'title' => t('Remove'),
        'url' => Url::fromRoute('entity.hidden_tab_mailer.delete_form', [
          'hidden_tab_mailer' => $entity->id(),
          'lredirect' => Utility::redirectHere(),
        ]),
      ];
      $v['operations']['#links']['edit'] = [
        'title' => t('Edit'),
        'url' => Url::fromRoute('entity.hidden_tab_mailer.edit_form', [
          'hidden_tab_mailer' => $entity->id(),
          'lredirect' => Utility::redirectHere(),
        ]),
      ];
      $table[$entity->id()] = $v;
    }
    $output['admin'][$this->id()] = $table;
  }


}
