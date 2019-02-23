<?php

namespace Drupal\hidden_tab\Entity\Helper;

/**
 * Helper for \Drupal\hidden_tab\Entity\Base\TimestampedEntityInterface.
 *
 * @see \Drupal\hidden_tab\Entity\Base\TimestampedEntityInterface
 */
trait TimestampedEntityTrait {

  /**
   * When entity was created.
   *
   * @var int
   */
  protected $created;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

}
