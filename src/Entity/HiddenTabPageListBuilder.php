<?php

namespace Drupal\hidden_tab\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\hidden_tab\Entity\Helper\ConfigListBuilderBase;
use Drupal\hidden_tab\FUtility;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginManager;
use Drupal\hidden_tab\Utility;

/**
 * Provides a listing of hidden_tab_pages entities.
 *
 * Also adds the 'layout edit' operation to the default operations.
 *
 * @see \Drupal\hidden_tab\Entity\HiddenTabPageInterface
 */
class HiddenTabPageListBuilder extends ConfigListBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['tab_uri'] = $this->t('Tab Uri');
    $header['secret_uri'] = $this->t('Secret Uri');
    $header['target_entity_type'] = $this->t('Type');
    $header['target_entity_bundle'] = $this->t('Bundle');
    $header['template'] = $this->t('Template');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  protected function unsafeBuildRow(EntityInterface $entity): array {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface $entity */
    $row = parent::configRowsBuilder($entity, [
      'id',
      'tab_uri',
      'secret_uri',
      'target_entity_type',
      'target_entity_bundle',
    ]);
    try {
      $plugins = HiddenTabTemplatePluginManager::man()->plugins();
      if ($entity->inlineTemplate()) {
        $row['template'] = $this->t('Inline');
      }
      elseif (isset($plugins[$entity->template()])) {
        $row['template'] = $plugins[$entity->template()]->label();
      }
      else {
        $row['template'] = $this->t('Missing: @missing', [
          '@missing' => $entity->template(),
        ]);
      }
    }
    catch (\Throwable $error0) {
      Utility::log($error0, 'hidden_tab_page', 'template');
      $row['template'] = Utility::WARNING;
    }
    return $row;
  }

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

}
