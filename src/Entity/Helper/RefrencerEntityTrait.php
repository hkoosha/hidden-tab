<?php

namespace Drupal\hidden_tab\Entity\Helper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Drupal\user\UserInterface;

/**
 * Helper for \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface.
 *
 * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface
 */
trait RefrencerEntityTrait {

  /**
   * See targetPageId()
   */
  protected $target_hidden_tab_page;

  /**
   * See targetPageEntity().
   *
   * @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetPageEntity()
   */
  protected $targetPageEntity;

  /**
   * See targetUserId()
   */
  protected $target_user;

  /**
   * See targetUserEntity().
   *
   * @var \Drupal\user\UserInterface
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetUserEntity()
   */
  protected $targetUserEntity;

  /**
   * See targetEntityId().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetEntityId()
   */
  protected $target_entity;

  /**
   * See targetEntity().
   *
   * @var \Drupal\Core\Entity\EntityInterface
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetEntity()
   */
  protected $targetEntity;

  /**
   * See targetEntityType().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetEntityType()
   */
  protected $target_entity_type;

  /**
   * See targetEntityBundle().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\Base\RefrencerEntityInterface::targetEntityBundle()
   */
  protected $target_entity_bundle;

  /**
   * {@inheritdoc}
   */
  public function targetPageId(): ?string {
    return $this->target_hidden_tab_page;
  }

  /**
   * {@inheritdoc}
   */
  public function targetPageEntity(): ?HiddenTabPageInterface {
    if (!isset($this->targetPageEntity) && $this->targetPageId() !== NULL && $this->targetPageId() !== '') {
      $this->targetPageEntity = \Drupal::entityTypeManager()
        ->getStorage('hidden_tab_page')
        ->load($this->targetPageId());
    }
    return $this->targetPageEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function targetUserId(): ?string {
    return $this->target_user;
  }

  /**
   * {@inheritdoc}
   */
  public function targetUserEntity(): ?UserInterface {
    if (!isset($this->targetUserEntity) && $this->targetUserId() !== NULL && $this->targetUserId() !== '') {
      $this->targetUserEntity = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($this->targetUserId());
    }
    return $this->targetUserEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function targetEntityId(): ?string {
    return $this->target_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function targetEntity(): ?EntityInterface {
    if (!isset($this->targetEntity) && $this->targetEntityId() && $this->targetEntityId() !== NULL && $this->targetEntityId() !== '') {
      $this->targetEntity = \Drupal::entityTypeManager()
        ->getStorage($this->targetEntityType())
        ->load($this->targetEntityId());
    }
    return $this->targetEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function targetEntityType(): ?string {
    return $this->target_entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function targetEntityBundle(): ?string {
    return $this->target_entity_bundle;
  }

}
