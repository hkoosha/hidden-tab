<?php

namespace Drupal\hidden_tab\Plugin\HiddenTabTemplate;

use Drupal\hidden_tab\Plugable\Annotation\HiddenTabTemplateAnon;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginBase;

/**
 * Displays email's title.
 *
 * @HiddenTabTemplateAnon(
 *   id = "hidden_tab_email_title"
 * )
 */
class HiddenTabEmailTitleTemplate extends HiddenTabTemplatePluginBase {

  /**
   * See id().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::id()
   */
  protected static $PID = 'hidden_tab_email_title';

  /**
   * See label().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::label()
   */
  protected static $HTPLabel = 'Email Title';

  /**
   * See description().
   *
   * @var string
   *
   * @see \Drupal\hidden_tab\HiddenTabTemplatePluginBase::description()
   */
  protected static $HTPDescription = 'Default template to render title of email';

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
  protected static $HTPTags = ['email', 'email_title'];

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
  protected $templateFile = 'internal-email-title';

}
