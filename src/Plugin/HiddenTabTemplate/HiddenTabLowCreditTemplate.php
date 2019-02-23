<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabTemplate;

use Drupal\hidden_tab\Plugable\Annotation\HiddenTabTemplateAnon;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginBase;

/**
 * Displays low credit warning to the user.
 *
 * @HiddenTabTemplateAnon(
 *   id = "hidden_tab_low_credit"
 * )
 */
class HiddenTabLowCreditTemplate extends HiddenTabTemplatePluginBase {

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::id()
   */
  protected static $PID = 'hidden_tab_low_credit';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::label()
   */
  protected static $HTPLabel = 'Low Credit Template';

  /**
   * See description().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::description()
   */
  protected static $HTPDescription = 'TODO';

  /**
   * See weight().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::weight()
   */
  protected static $HTPWeight = 0;

  /**
   * See display().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::display()
   */
  protected static $HTPDisplay = FALSE;

  /**
   * See tags().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\HiddenTabPluginInterfaceBase::tags()
   */
  protected static $HTPTags = ['credit'];

  /**
   * See regions().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginBase::regions()
   */
  protected $regions = [];

  /**
   * See templateFile().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginBase::templateFile()
   */
  protected $templateFile = 'internal-low-credit';

}
