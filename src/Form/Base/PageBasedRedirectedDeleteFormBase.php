<?php

namespace Drupal\hidden_tab\Form\Base;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\hidden_tab\Utility;

class PageBasedRedirectedDeleteFormBase extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    if (Utility::checkRedirect()) {
      $form_state->setRedirectUrl(Utility::checkRedirect());
    }
  }
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    /** @var \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface $entity */
    $entity = $this->getEntity();
    if(!$entity->targetPageId()) {
      return parent::getCancelUrl();
    }
    return Url::fromRoute('entity.hidden_tab_page.layout_form', [
      'hidden_tab_page' => $entity->targetPageId(),
      'lredirect' => Utility::lRedirect(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    /** @var \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface $entity */
    $entity = $this->getEntity();
    if(!$entity->targetPageId()) {
      return parent::getCancelUrl();
    }
    if ($entity->targetPageId() && Utility::lRedirect() && Utility::checkRedirect()) {
      return Url::fromRoute(
        'entity.hidden_tab_page.layout_form', [
          'hidden_tab_page' => $entity->targetPageId(),
          'lredirect' => Utility::lRedirect(),
        ]
      );
    }
    elseif ($entity->targetPageId()) {
      return Url::fromRoute('entity.hidden_tab_page.layout_form', [
        'hidden_tab_page' => $entity->targetPageId(),
      ]);
    }
    else {
      return parent::getRedirectUrl();
    }
  }

}