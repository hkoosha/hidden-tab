<?php

namespace Drupal\hidden_tab\Entity;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\hidden_tab\Entity\Helper\EntityListBuilderBase;
use Drupal\hidden_tab\FUtility;
use Drupal\hidden_tab\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the hidden tab mailer entity type.
 */
class HiddenTabMailerListBuilder extends EntityListBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              Connection $database,
                              LoggerChannel $logger,
                              RedirectDestinationInterface $redirect_destination) {
    parent::__construct($entity_type, $storage, $database, $logger, $redirect_destination);
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container,
                                        EntityTypeInterface $entity_type) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('database'),
      $container->get('logger.factory')->get('hidden_tab'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return static::header() + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  protected function unsafeBuildRow(EntityInterface $entity) {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $entity */
    return $this->row($entity);
  }

  /**
   * Header for row().
   *
   * @return array
   *   An array for row() headers.
   */
  public static function header(): array {
    $header = FUtility::refrencerEntityRowBuilderForEntityListHeaders();
    $header['email_schedule'] = t('Schedule');
    $header['next_schedule'] = t('Upcoming');
    return $header;
  }

  /**
   * Helper to create a renderable row output of the entity.
   *
   * @param \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $entity
   *   Entity to render.
   *
   * @return array
   *   Renderable array output.
   */
  public static function row(HiddenTabMailerInterface $entity) {
    $row = FUtility::refrencerEntityRowBuilderForEntityList($entity, 'hidden_tab_credit');
    try {
      // Email schedule.
      try {
        $s = (int) $entity->emailSchedule();
        $g = $entity->emailScheduleGranul() . ($s > 1 ? 's' : '');
        $row['email_schedule'] = $s . ' ' . $g;
      }
      catch (\Throwable $error0) {
        Utility::log($error0, 'hidden_tab_mailer', 'email_schedule');
        $row['email_schedule'] = Utility::WARNING;
      }
      // Next schedule.
      try {
        if ($entity->nextSchedule()) {
          $df = \Drupal::service('date.formatter');
          $row['next_schedule'] = $df->format($entity->nextSchedule());
        }
        else {
          $row['next_schedule'] = Utility::CROSS;
        }
      }
      catch (\Throwable $error0) {
        Utility::log($error0, 'hidden_tab_mailer', 'next_schedule');
        $row['next_schedule'] = Utility::WARNING;
      }
    }
    catch (\Throwable $error_x) {
      Utility::log($error_x, 'hidden_tab_mailer', 'email/next schedule');
      $row['email_schedule'] = Utility::WARNING;
      $row['next_schedule'] = Utility::WARNING;
    }
    return $row;
  }

}
