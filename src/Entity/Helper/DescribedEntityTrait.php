<?php
/**
 * Created by PhpStorm.
 * User: milan
 * Date: 2/2/19
 * Time: 2:03 AM
 */

namespace Drupal\hidden_tab\Entity\Helper;

/**
 * Helper for \Drupal\hidden_tab\Entity\Base\DescribedEntityTrait.
 *
 * @see \Drupal\hidden_tab\Entity\Base\DescribedEntityTrait
 */
trait DescribedEntityTrait {

  /**
   * See description().
   *
   * @var string
   *   See description().
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function description(): ?string {
    return $this->description;
  }

}
