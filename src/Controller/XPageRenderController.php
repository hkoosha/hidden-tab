<?php

namespace Drupal\hidden_tab\Controller;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\hidden_tab\Entity\HiddenTabPageAccessControlHandler;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Plugable\Render\HiddenTabRenderPluginManager;
use Drupal\hidden_tab\Utility;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class XPageRenderController, the class which renders the hidden tabs.
 *
 * @package Drupal\hidden_tab\Controller
 */
class XPageRenderController extends ControllerBase implements ContainerInjectionInterface {

  const FS_OPEN = 'hidden_tab_on_page_admin_open';

  const ADMIN = '';

  /**
   * To find the entity in the Uri.
   *
   * @var string
   */
  protected $currentPath;

  /**
   * Params including secret provided by user in the query.
   *
   * @var \Symfony\Component\HttpFoundation\ParameterBag
   */
  protected $query;

  /**
   * To log.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(string $current_path,
                              ParameterBag $query,
                              LoggerChannel $logger) {
    $this->currentPath = $current_path;
    $this->query = $query;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Symfony\Component\HttpFoundation\Request $cr */
    $cr = $container->get('request_stack')->getCurrentRequest();
    return new static(
      \Drupal::request()->getPathInfo(),
      $cr->query,
      $container->get('logger.factory')->get('hidden_tab')
    );
  }

  /**
   * Displays the actual page, called from Tab page.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return array
   *   Render array of komponents to put in the regions, as configured in the
   *   page's layout.
   */
  public function display(NodeInterface $node): array {
    /** @var HiddenTabPageInterface $page */
    $entity = $node;

    $path = explode('/', $this->currentPath);
    if (count($path) !== 4 || ((((int) $path[2]) . '') !== ($path[2] . '')) || $path[1] !== 'node') {
      throw new NotFoundHttpException();
    }
    $page = Utility::pageByTabUri($path[3]);
    $type = NULL;
    if ($page) {
      $type = 'tab';
    }
    if (!$page) {
      $page = Utility::pageBySecretUri($path[3]);
      $type = 'uri';
    }
    if (!$page) {
      throw new NotFoundHttpException();
    }

    if ($type === 'uri') {
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      $can = $page->access(
        HiddenTabPageAccessControlHandler::OP_VIEW_SECRET_URI,
        $this->currentUser,
        TRUE,
        $entity,
        $this->query);
      if (!$can->isAllowed()) {
        if ($page->isAccessDenied()) {
          /** @noinspection PhpUndefinedMethodInspection */
          throw new AccessDeniedHttpException(
            $page->isAccessDenied() && ($can instanceof AccessResultInterface)
              ? $can->getReason() :
              ''
          );
        }
        else {
          throw new NotFoundHttpException();
        }
      }
    }


    $output['admin'] = [
      '#type' => 'details',
      '#open' => TRUE || isset($_SESSION[self::FS_OPEN]) ? TRUE || $_SESSION[self::FS_OPEN] : FALSE,
      '#title' => $this->t('Admin'),
    ];
    foreach (HiddenTabRenderPluginManager::man()->pluginsSorted() as $plugin) {
      /** @var \Drupal\hidden_tab\Plugable\Render\HiddenTabRenderInterface $plugin */
      if ($plugin->access($entity, $page, $this->currentUser())->isAllowed()) {
        try {
          $plugin->render($entity, $page, $this->currentUser(), $this->query, $output);
        }
        catch (\Throwable $err) {
          if ($this->currentUser->hasPermission('administer site configuration')) {
            $this->messenger()
              ->addError(t('site encountered an error rendering this page, visit the logs for more information: @msg', [
                '@msg' => $err->getMessage(),
              ]));
          }
          $this->logger->error('error while rendering page, entity={entity} entity-type={type} page={page} msg={msg} trace={trace}', [
            'entity' => $entity->id(),
            'type' => $entity->getEntityTypeId(),
            'page' => $page->id(),
            'msg' => $err->getMessage(),
            'trace' => $err->getTraceAsString(),
          ]);
        }
      }
    }
    if (!$this->currentUser()->hasPermission(HiddenTabPageAccessControlHandler::PERMISSION_VIEW_ON_PAGE_ADMIN_STUFF)) {
      unset($output['admin']);
    }
    $output['admin']['#open'] = TRUE;
    return $output;
  }

}
