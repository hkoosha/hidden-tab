<?php

namespace Drupal\hidden_tab\Entity\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Routing\RedirectDestinationInterface;

abstract class EntityListBuilderBase extends EntityListBuilder {

  /**
   * Used by render().
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Log thingy.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              Connection $database,
                              LoggerChannel $logger,
                              RedirectDestinationInterface $redirect_destination) {
    parent::__construct($entity_type, $storage);
    $this->database = $database;
    $this->logger = $logger;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->database
      ->query('SELECT COUNT(*) FROM {' . $this->entityTypeId . '}')
      ->fetchField();

    $build['summary']['#markup'] = $this->t('Total: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public final function buildRow(EntityInterface $entity) {
    return $this->unsafeBuildRow($entity) + parent::buildRow($entity);
  }

  protected abstract function unsafeBuildRow(EntityInterface $entity);

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    return $operations;
  }

}
