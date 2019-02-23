<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabAccess;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageAccessControlHandler;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\hidden_tab\Plugable\Access\HiddenTabAccessPluginBase;
use Drupal\hidden_tab\Plugable\Annotation\HiddenTabAccessAnon;
use Drupal\hidden_tab\Service\CreditCharging;
use Drupal\hidden_tab\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Plugin implementation of the hidden_tab_secret_uri.
 *
 * @HiddenTabAccessAnon(
 *   id = "hidden_tab_credit"
 * )
 */
class HiddenTabCreditPermission extends HiddenTabAccessPluginBase {

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::id()
   */
  protected static $PID = 'hidden_tab_credit';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::label()
   */
  protected static $HTPLabel = 'Credit';

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
  protected static $HTPWeight = 3;

  /**
   * See tags().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::tags()
   */
  protected static $HTPTags = [];

  /**
   * Well, to charge user for credit.
   *
   * @var CreditCharging
   */
  protected $creditChargingService;

  /**
   * To log.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * To translate.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $t;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              string $plugin_id,
                              $plugin_definition,
                              LoggerChannel $logger,
                              TranslationInterface $t,
                              CreditCharging $credit_charging) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->creditChargingService = $credit_charging;
    $this->logger = $logger;
    $this->t = $t;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('hidden_tab'),
      $container->get('string_translation'),
      $container->get('hidden_tab.credit_service')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function canAccess(EntityInterface $context_entity,
                            AccountInterface $account,
                            ?HiddenTabPageInterface $page,
                            ParameterBag $query,
                            string $access): AccessResult {
    if (!$page
      || $access !== HiddenTabPageAccessControlHandler::PERMISSION_VIEW_SECRET_URI
      || !$query->get(Utility::QUERY_NAME)) {
      return AccessResult::neutral();
    }
    $hash = $query->get(Utility::QUERY_NAME);

    if($account->hasPermission('administer site configuration')) {
      return AccessResult::allowed();
    }

    foreach ($page->creditCheckOrder() as $order) {
      switch ($order) {
        case 'peu':
          foreach ($this->creditChargingService->peu($page, $context_entity, $account) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'pex':
          foreach ($this->creditChargingService->pex($page, $context_entity, FALSE) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'pxu':
          foreach ($this->creditChargingService->pxu($page, FALSE, $account) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'pxx':
          foreach ($this->creditChargingService->pxx($page, FALSE, FALSE) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'xeu':
          foreach ($this->creditChargingService->xeu(FALSE, $context_entity, $account) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'xex':
          foreach ($this->creditChargingService->xex(FALSE, $context_entity, FALSE) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        case 'xxu':
          foreach ($this->creditChargingService->xxu(FALSE, FALSE, $account) as $h) {
            if (Utility::matches($h, $context_entity, $hash)) {
              $charge = $this->creditChargingService->charge($h, $account);
              if ($charge) {
                return AccessResult::allowed();
              }
            }
          }
          break;

        default:
          $this->logger->error('Illegal state when checking access by HiddenTabCreditPermission, entity-type={type} entity={id} access={}', [
            'type' => $context_entity->getEntityTypeId(),
            'id' => $context_entity->id(),
            'access' => '',
          ]);
          throw new \RuntimeException('illegal state');
      }
    }

    return AccessResult::neutral($this->t->translate('You do not have enough credit to access this page.'));
  }

}
