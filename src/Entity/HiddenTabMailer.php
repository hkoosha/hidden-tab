<?php

namespace Drupal\hidden_tab\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hidden_tab\Entity\Helper\DescribedEntityTrait;
use Drupal\hidden_tab\Entity\Helper\MultiAspectPluginSupportingTrait;
use Drupal\hidden_tab\Entity\Helper\RefrencerEntityTrait;
use Drupal\hidden_tab\Entity\Helper\StatusedEntityTrait;
use Drupal\hidden_tab\Entity\Helper\TimestampedEntityTrait;
use Drupal\hidden_tab\FUtility;
use Drupal\hidden_tab\Plugable\MailDiscovery\HiddenTabMailDiscoveryPluginManager;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginManager;

/**
 * Defines the hidden tab mailer entity class.
 *
 * @ContentEntityType(
 *   id = "hidden_tab_mailer",
 *   label = @Translation("Hidden Tab Mailer"),
 *   label_collection = @Translation("Hidden Tab Mailers"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\hidden_tab\Entity\HiddenTabMailerListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" =
 *   "Drupal\hidden_tab\Entity\HiddenTabMailerAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\hidden_tab\Form\HiddenTabMailerForm",
 *       "edit" = "Drupal\hidden_tab\Form\HiddenTabMailerForm",
        "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "hidden_tab_mailer",
 *   data_table = "hidden_tab_mailer_field_data",
 *   admin_permission = "administer hidden tab mailer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/hidden-tab-mailer/add",
 *     "canonical" = "/hidden_tab_mailer/{hidden_tab_mailer}",
 *     "edit-form" =
 *   "/admin/content/hidden-tab-mailer/{hidden_tab_mailer}/edit",
 *     "delete-form" =
 *   "/admin/content/hidden-tab-mailer/{hidden_tab_mailer}/delete",
 *     "collection" = "/admin/content/hidden-tab-mailer"
 *   },
 *   field_ui_base_route = "entity.hidden_tab_mailer.settings"
 * )
 */
class HiddenTabMailer extends ContentEntityBase implements HiddenTabMailerInterface {

  use RefrencerEntityTrait;
  use StatusedEntityTrait;
  use DescribedEntityTrait;
  use TimestampedEntityTrait;
  use EntityChangedTrait;
  use MultiAspectPluginSupportingTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += FUtility::defaultField();
    $fields += FUtility::refrencerEntityFields('node', t('Node'), FUtility::nodeBundlesSelectList());

    $fields['plugin'] = FUtility::list('Email Discovery Plugin', NULL,
      HiddenTabMailDiscoveryPluginManager::man()->pluginsForSelectElement());
    $fields['email_schedule'] =
      FUtility::int('Email Schedule', 'Every...');
    $fields['email_schedule_granul'] =
      FUtility::list('Email Schedule Granularity', NULL, [
        'second' => 'Second',
        'minute' => 'Minute',
        'hour' => 'Hour',
        'day' => 'Day',
        'month' => 'Month',
        'year' => 'Year',
        'week' => 'Week',
      ]);
    $fields['next_schedule'] =
      FUtility::timestamp('next_schedule', '');
    $fields['email_template'] =
      FUtility::list('Email Template', NULL,
        HiddenTabTemplatePluginManager::man()
          ->pluginsForSelectElement('mailer'))
        ->setRequired(FALSE);
    $fields['email_title_template'] =
      FUtility::list('Email Title Template', NULL,
        HiddenTabTemplatePluginManager::man()
          ->pluginsForSelectElement('mailer'))
        ->setRequired(FALSE);
    $fields['email_inline_template'] =
      FUtility::textArea('Email Inline Twig Template', NULL);
    $fields['email_inline_title_template'] =
      FUtility::textArea('Email Inline Title Twig Template', NULL);

    return $fields;
  }

  /**
   * {@inheritdoc}
   *
   * When a new hidden tab mailer entity is created, set the uid entity
   * reference to the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * Small amount of elements to create a new entity.
   *
   * Those values who usually have sane defaults are omitted. So is the
   * target page as it is usually known.
   *
   * @param \Drupal\Core\Form\FormStateInterface|null $form_state
   *   Submitted form, if any.
   * @param string $prefix
   *   The namespace to prefix form elements with.
   * @param bool $add_targets
   *   Add refrencer fields or not.
   *
   * @return array
   *   The form elements.
   */
  public static function littleForm(?FormStateInterface $form_state,
                                    string $prefix = '',
                                    bool $add_targets = TRUE): array {

    $form[$prefix . 'plugin'] = [
      '#type' => 'select',
      '#title' => t('Mail Discovery Plugin'),
      '#description' => t('The plugin used to find the email address.'),
      '#options' => HiddenTabMailDiscoveryPluginManager::man()
        ->pluginsForSelectElement(NULL, TRUE),
      '#default_value' => NULL,
    ];

    foreach (HiddenTabMailDiscoveryPluginManager::man()->plugins() as $plugin) {
      $plugin->handleConfigForm($form, $form_state, NULL);
    }

    $form[$prefix . 'email_template'] = [
      '#type' => 'textarea',
      '#title' => t('Email template'),
      '#description' => t('Twig template used to render email body. Available variables: email, mailer, page, entity, subject'),
      '#options' => HiddenTabTemplatePluginManager::man()
        ->pluginsForSelectElement('email', TRUE),
    ];
    foreach ($form[$prefix . 'email_template']['#options'] as $key => $label) {
      $form[$prefix . 'email_template']['#default_value'] = $key;
      break;
    }

    $form[$prefix . 'email_title_template'] = [
      '#type' => 'textarea',
      '#title' => t('Email title template'),
      '#description' => t('Twig template used to render email title. Available variables: email, mailer, page, entity.'),
      '#options' => HiddenTabTemplatePluginManager::man()
        ->pluginsForSelectElement('email', TRUE),
    ];
    foreach ($form[$prefix . 'email_title_template']['#options'] as $key => $label) {
      $form[$prefix . 'email_title_template']['#default_value'] = $key;
      break;
    }

    $form[$prefix . 'email_schedule'] = [
      '#type' => 'number',
      '#title' => t('Email schedule'),
      '#description' => t('Send every...? Zero disables.'),
      '#default_value' => HiddenTabMailerInterface::EMAIL_SCHEDULE_DEFAULT_MONTHS,
      '#min' => 0,
    ];

    $form[$prefix . 'email_schedule_granul'] = [
      '#type' => 'select',
      '#title' => t('Email schedule granularity'),
      '#default_value' => HiddenTabMailerInterface::EMAIL_SCHEDULE_DEFAULT_GRANULARITY,
      '#options' => [
        'second' => t('Seconds'),
        'minute' => t('minutes'),
        'hour' => t('Hours'),
        'day' => t('days'),
        'month' => t('months'),
        'year' => t('Years'),
        'week' => t('Weeks'),
      ],
    ];

    $form[$prefix . 'next_schedule'] = [
      '#type' => 'timestamp',
      '#title' => t('Upcoming'),
      '#default_value' => -1,
      '#description' => t('The next date email will be sent on.'),
    ];

    if ($add_targets) {
      return $form + FUtility::refrencerEntityFormElements($prefix);
    }
    else {
      return $form;
    }
  }

  /**
   * Extract values of a submitted form for credit creation.
   *
   * @param string $prefix
   *   Namespace prefix of form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Submitted form.
   * @param bool $extract_refs
   *   Extract refrencer fields or not.
   *
   * @return array
   *   Extracted values, or sane defaults.
   */
  public static function extractFormValues(FormStateInterface $form_state,
                                           string $prefix,
                                           bool $extract_refs): array {
    $schedule = $form_state->getValue($prefix . 'email_schedule');
    if ($schedule === '0') {
      $schedule = 0;
    }
    if ($schedule !== 0 && !$schedule) {
      $schedule = HiddenTabMailerInterface::EMAIL_SCHEDULE_DEFAULT_MONTHS;
    }
    $v = [
      'email_schedule' => $schedule,
      'email_schedule_granularity' => $form_state->getValue('email_schedule_granularity')
        ?: HiddenTabMailerInterface::EMAIL_SCHEDULE_DEFAULT_GRANULARITY,
    ];

    $plugin_ = $form_state->getValue($prefix. 'plugin');
    if ($plugin_) {
      $plugin = HiddenTabMailDiscoveryPluginManager::man()->plugin($plugin_);
      $f = NULL;
      $config = $plugin->handleConfigFormSubmit($f, $form_state, NULL);
      $v += [
        'plugins' => json_encode([
          $plugin->id() => $config,
        ]),
      ];
    }
    else {
      $v += [
        'plugins' => json_encode([

        ]),
      ];
    }
    if ($extract_refs) {
      $v += FUtility::extractRefrencerValues($form_state, $prefix);
    }

    $v += FUtility::extractDefaultEntityValues($form_state, $prefix);
    if ($extract_refs) {
      $v += FUtility::extractRefrencerValues($form_state, $prefix);
    }

    return $v;
  }

  public static function validateForm(FormStateInterface $form_state,
                                      string $prefix,
                                      string $target_entity_type,
                                      bool $validate_targets,
                                      ?string $current_editing_entity_id_if_any): bool {
    if (!$validate_targets) {
      // Nothing to do, for now.
      return TRUE;
    }
    return FUtility::entityCreationValidateTargets($form_state,
      $prefix,
      $target_entity_type,
      $current_editing_entity_id_if_any,
      function (?HiddenTabPageInterface $page, ?EntityInterface $entity, ?AccountInterface $user): array {
        return \Drupal::service('hidden_tab.credit_service')
          ->he($page, $entity, $user);
      });
  }

  /**
   * See emailSchedule().
   *
   * @var int|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailSchedule()
   */
  protected $email_schedule;

  /**
   * See emailScheduleGranul().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailScheduleGranul()
   */
  protected $email_schedule_granul;

  /**
   * See nextSchedule().
   *
   * @var int|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::nextSchedule()
   */
  protected $next_schedule;

  /**
   * See emailTemplate().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailTemplate()
   */
  protected $email_template;

  /**
   * See emailTitleTemplate().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailTitleTemplate()
   */
  protected $email_title_template;

  /**
   * See emailInlineTemplate().
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailInlineTemplate().
   */
  protected $email_inline_template;

  /**
   * See emailTitleInlineTemplate()
   *
   * @var string|null
   *
   * @see \Drupal\hidden_tab\Entity\HiddenTabMailerInterface::emailTitleInlineTemplate()
   */
  protected $email_title_inline_template;

  /**
   * {@inheritdoc}
   */
  public function emailSchedule(): ?int {
    return $this->email_schedule;
  }

  /**
   * {@inheritdoc}
   */
  public function emailScheduleGranul(): ?string {
    return $this->email_schedule_granul;
  }

  /**
   * {@inheritdoc}
   */
  public function nextSchedule(): ?int {
    return $this->next_schedule;
  }

  /**
   * {@inheritdoc}
   */
  public function emailTemplate(): ?string {
    return $this->email_template;
  }

  /**
   * {@inheritdoc}
   */
  public function emailTitleTemplate(): ?string {
    return $this->email_title_template;
  }

  /**
   * {@inheritdoc}
   */
  public function emailInlineTemplate(): ?string {
    return $this->email_inline_template;
  }

  /**
   * {@inheritdoc}
   */
  public function emailTitleInlineTemplate(): ?string {
    return $this->email_title_inline_template;
  }

}
