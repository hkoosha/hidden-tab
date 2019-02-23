<?php

namespace Drupal\hidden_tab\Entity\Helper;

/**
 * Helper for \Drupal\hidden_tab\Entity\Base\StatusedEntityInterface.
 *
 * @see \Drupal\hidden_tab\Entity\Base\StatusedEntityInterface
 */
trait StatusedEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    // FIXME
    return TRUE;
    //    return $this->get('status');
  }

  /**
   * {@inheritdoc}
   */
  public function enable() {
    return $this->set('status', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function disable() {
    return $this->set('status', FALSE);
  }

}
