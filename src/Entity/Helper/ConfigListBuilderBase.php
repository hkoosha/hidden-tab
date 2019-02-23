<?php

namespace Drupal\hidden_tab\Entity\Helper;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\hidden_tab\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ConfigListBuilderBase extends ConfigEntityListBuilder {

  /**
   * To see what operations user has access to.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $current_user;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              AccountProxyInterface $current_user) {
    parent::__construct($entity_type, $storage);
    $this->current_user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container,
                                        EntityTypeInterface $entity_type) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public final function buildRow(EntityInterface $entity) {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface $entity */
    try {
      return $this->unsafeBuildRow($entity) + parent::buildRow($entity);
    }
    catch (\Throwable $error0) {
      Utility::log($error0, $this->entityTypeId, '~');
      $ret['label'] = $entity->id();
      for ($i = 0; $i < (count($this->buildHeader()) - 1); $i++) {
        $ret[] = Utility::CROSS;
      }
      return $ret;
    }
  }

  /**
   * Helper for buildRow().
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to build row from.
   *
   * @return array
   *   Built row.
   */
  protected abstract function unsafeBuildRow(EntityInterface $entity): array;

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $op = parent::getOperations($entity);
    // Layouts is a VERY dangerous page. It gives access to EVERYTHING.
    if ($this->current_user
      ->hasPermission('administer site configuration')) {
      $layout = [
        'layout' => [
          'title' => $this->t('Layout'),
          'weight' => 1,
          'url' => Url::fromRoute('entity.hidden_tab_page.layout_form',
            ['hidden_tab_page' => $entity->id()]),
        ],
      ];
      $op = $layout + $op;
    }
    return $op;
  }

  protected function configRowsBuilder(ConfigEntityInterface $entity, array $props) {
    /** @var \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface $entity */
    $ret = [];
    foreach ($props as $prop) {
      switch ($prop) {
        case 'target_user':
          try {
            if (!$entity->targetUserId()) {
              $row['target_user'] = Utility::CROSS;
            }
            else {
              $row['target_user'] = Link::createFromRoute(
                $entity->targetUserEntity()->label(),
                'entity.user.canonical',
                [
                  'user' => $entity->targetUserId(),
                ]
              );
            }
          }
          catch (\Throwable $error0) {
            Utility::log($error0, $entity->getEntityTypeId(), 'target_user');
            try {
              $row['target_user'] = $entity->targetUserId();
            }
            catch (\Throwable $error1) {
              Utility::log($error1, $entity->getEntityTypeId(), 'target_user');
              $row['target_user'] = Utility::WARNING;
            }
          }
          break;

        case 'target_entity':
          try {
            if (!$entity->targetEntityId()) {
              $row['target_entity'] = Utility::CROSS;
            }
            else {
              $row['target_entity'] = Link::createFromRoute(
                $entity->targetEntity()->label(),
                'entity.' . $entity->targetEntityType() . '.canonical',
                [
                  $entity->targetEntityType() => $entity->targetUserId(),
                ]
              );
            }
          }
          catch (\Throwable $error0) {
            Utility::log($error0, $entity->getEntityTypeId(), 'target_entity');
            try {
              $row['target_entity'] = $entity->targetEntityId();
            }
            catch (\Throwable $error1) {
              Utility::log($error1, $entity->getEntityTypeId(), 'target_entity');
              $row['target_entity'] = Utility::WARNING;
            }
          }
          break;

        case 'target_hidden_tab_page':
          try {
            if (!$entity->targetPageId()) {
              $row['target_hidden_tab_page'] = Utility::CROSS;
            }
            else {
              $row['target_hidden_tab_page'] = Link::createFromRoute(
                $entity->targetPageEntity()->label(),
                'entity.hidden_tab_page.edit_form',
                [
                  'hidden_tab_page' => $entity->targetPageId(),
                ]
              );
            }
          }
          catch (\Throwable $error0) {
            Utility::log($error0, $entity->getEntityTypeId(), 'target_hidden_tab_page');
            try {
              $row['target_hidden_tab_page'] = $entity->targetPageId();
            }
            catch (\Throwable $error1) {
              Utility::log($error1, $entity->getEntityTypeId(), 'target_hidden_tab_page');
              $row['target_hidden_tab_page'] = Utility::WARNING;
            }
          }
          break;

        default:
          try {
            $ret[$prop] = $entity->get($prop) ?: Utility::CROSS;
          }
          catch (\Throwable $error1) {
            Utility::log($error1, $entity->getEntityTypeId(), $prop);
            $ret[$prop] = Utility::CROSS;
          }
      }
    }
    return $ret;
  }


}