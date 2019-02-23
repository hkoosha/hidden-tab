<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabRender;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Entity\HiddenTabPlacementAccessControlHandler;
use Drupal\hidden_tab\Entity\HiddenTabPlacementInterface;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabRenderAnon;
use Drupal\hidden_tab\Plugable\Komponent\HiddenTabKomponentPluginManager;
use Drupal\hidden_tab\Plugable\Render\HiddenTabRenderPluginBase;
use Drupal\hidden_tab\Plugable\Render\HiddenTabRenderSafeTrait;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginManager;
use Drupal\hidden_tab\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Renders the actual page
 *
 * @HiddenTabRenderAnon(
 *   id = "hidden_tab_page"
 * )
 */
class HiddenTabRenderPage extends HiddenTabRenderPluginBase {

  use HiddenTabRenderSafeTrait;

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::id()
   */
  protected static $PID = 'hidden_tab_page';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::label()
   */
  protected static $HTPLabel = 'Page';

  /**
   * See description().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::description()
   */
  protected static $HTPDescription = 'Renders the actual page.';

  /**
   * See weight().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::weight()
   */
  protected static $HTPWeight = 10;

  /**
   * See tags().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::tags()
   */
  protected static $HTPTags = [];

  /**
   * To log!.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * To load placements.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $placementStorage;

  /**
   * Current path.
   *
   * @var string
   */
  protected $pathInfo;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              string $plugin_id,
                              $plugin_definition,
                              LoggerChannel $logger,
                              string $path_info,
                              EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->pathInfo = $path_info;
    $this->placementStorage = $storage;
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
      $container->get('logger.factory')->get('hidden_tab'),
      $container->get('request_stack')->getCurrentRequest()->getPathInfo(),
      $container->get('entity_type.manager')
        ->getStorage('hidden_tab_placement')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity,
                         HiddenTabPageInterface $page,
                         AccountInterface $user): AccessResult {
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  /** @noinspection PhpMethodParametersCountMismatchInspection */
  public function render0(EntityInterface $entity,
                          HiddenTabPageInterface $page,
                          AccountInterface $user,
                          ParameterBag $bag,
                          array &$output) {
    /** @var \Drupal\hidden_tab\Plugable\Komponent\HiddenTabKomponentInterface[] $komponent_types */
    /** @var \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplateInterface $admin_template */

    $komponent_types = HiddenTabKomponentPluginManager::man()->plugins();

    $placements = Utility::placementsOfPage($this->placementStorage, $page->id());
    $placements = array_filter($placements, function (HiddenTabPlacementInterface $p) use ($user, $entity, $bag): bool {
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      return $p->access(HiddenTabPlacementAccessControlHandler::OP_VIEW,
        $user, FALSE, $entity, $bag);
    });
    $placements = array_filter($placements, function (HiddenTabPlacementInterface $p) use ($komponent_types) : bool {
      if (isset($komponent_types[$p->komponentType()])) {
        return TRUE;
      }
      \Drupal::logger('hidden_tab')
        ->warning('unsupported komponent type skipped: {type}',
          ['type' => $p->komponentType()]);
      return FALSE;
    });
    $placements = array_filter($placements, function (HiddenTabPlacementInterface $p) use ($entity): bool {
      if ($p->targetEntityType() && $entity->getEntityTypeId() !== $p->targetEntityType()) {
        return FALSE;
      }
      elseif ($p->targetEntityBundle() && $entity->bundle() !== $p->targetEntityBundle()) {
        return FALSE;
      }
      elseif ($p->targetEntityId() && $p->targetEntityId() !== $entity->id()) {
        return FALSE;
      }
      elseif ($p->targetUserId() && $p->targetUserId() !== $entity->id()) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    });
    usort($placements, function (HiddenTabPlacementInterface $p0, HiddenTabPlacementInterface $p1) {
      return $p0->weight() < $p1->weight();
    });

    $render = [];
    foreach ($placements as $placement) {
      try {
        $komponent_rendered = $komponent_types[$placement->komponentType()]
          ->render($entity, $page, $placement);
      }
      catch (\Throwable $t) {
        $this->logger
          ->warning('error while rendering komponent, page={page} entity={entity} template={template} path={path} user={user} komponent={k} msg={msg} trace={trace}', [
            'page' => $page->id(),
            'entity' => $entity->id(),
            'template' => $page->template(),
            'path' => $this->pathInfo,
            'user' => $user->id(),
            'k' => $placement->komponentType(),
            'msg' => $t->getMessage(),
            'trace' => $t->getTraceAsString(),
          ]);
        $komponent_rendered = t('Error');
      }
      if ($komponent_rendered === NULL) {
        // pass
      }
      elseif (gettype($komponent_rendered) === 'string') {
        $render[$placement->region()][] = [
          '#type' => 'markup',
          '#markup' => $komponent_rendered,
        ];
      }
      elseif (gettype($komponent_rendered) === 'array') {
        $render[$placement->region()][] = $komponent_rendered;
      }
      else {
        throw new \LogicException('unknown komponent return type: ' . gettype($komponent_rendered));
      }
    }

    $render = (object) $render;
    $page_r = NULL;
    if (!$page->template() || $page->inlineTemplate()) {
      $page_r = [
        '#type' => 'inline_template',
        '#template' => $page->inlineTemplate(),
        '#context' => [
          'regions' => $render,
          'current_user' => $user,
        ],
      ];
    }
    else {
      if (HiddenTabTemplatePluginManager::man()->exists($page->template())) {
        /** @var \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplateInterface $a */
        $a = HiddenTabTemplatePluginManager::man()->plugin($page->template());
        $page_r = [
          '#attached' => $a->attachLibrary(),
          '#theme' => 'hidden_tab_' . $a->id(),
          '#regions' => $render,
        ];
      }
      else {
        $this->logger
          ->warning('Template plugin not found while rendering page. page={hidden_tab_page} entity={entity} template={template}, path={path}, user={user}', [
            'hidden_tab_page' => $page->id(),
            'entity' => $entity->id(),
            'template' => $page->template(),
            'path' => $this->pathInfo,
            'user' => $user->id(),
          ]);
        $page_r = [
          '#type' => 'markup',
          '#markup' => $this->t('Error'),
        ];
      }
    }

    $output[$this->id()] = $page_r;
  }

}
