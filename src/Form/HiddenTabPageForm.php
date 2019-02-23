<?php

namespace Drupal\hidden_tab\Form;

use Drupal\Component\Uuid\Php;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hidden_tab\Event\HiddenTabPageFormEvent;
use Drupal\hidden_tab\FUtility;
use Drupal\hidden_tab\Plugable\Template\HiddenTabTemplatePluginManager;
use Drupal\hidden_tab\Utility;
use Drupal\user\PermissionHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Hidden Tab Page entity add/edit form..
 *
 * @property \Drupal\hidden_tab\Entity\HiddenTabPageInterface $entity
 *
 * @see \Drupal\hidden_tab\Entity\HiddenTabPageInterface
 */
class HiddenTabPageForm extends EntityForm {

  const DEFAULT_CREDIT_CHECK_ORDER = 'xex pex pxx xeu peu pxu xxu';

  const TAB_PERMISSION_DEFAULT_PERMISSION = 'administer site configuration';

  /**
   * User permission service.
   *
   * To provide a list of permissions for select list.
   *
   * @var \Drupal\user\PermissionHandler
   *
   * @see \Drupal\hidden_tab\Form\HiddenTabPageForm::form()
   */
  protected $userPermissionService;

  /**
   * Event dispatcher service, to dispatch the form and it's event to plugins.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   *
   * @see \Drupal\hidden_tab\Event\HiddenTabPageFormEvent
   */
  protected $eventer;

  /**
   * To get list of bundles of an entity type.
   *
   * Needed to limit the page to specific bundle.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   *
   * @see \Drupal\hidden_tab\Form\HiddenTabPageForm::form()
   */
  protected $bundleInfo;

  /**
   * To find of placements of a page, in case of template change.
   *
   * We have to reset placement's region in case page's template changes and
   * the old region is no longer available.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $hiddenTabPlacementStorage;

  /**
   * Handy service for creating the event.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuid;

  /**
   * Handy service for creating the event.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Handy service for creating the event.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $creditStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(PermissionHandler $user_permission_service,
                              EventDispatcherInterface $eventer,
                              EntityTypeBundleInfo $bundle_info,
                              EntityStorageInterface $hidden_tab_placement_storage,
                              Php $uuid,
                              FormBuilderInterface $form_builder,
                              EntityStorageInterface $credit_storage) {
    $this->userPermissionService = $user_permission_service;
    $this->eventer = $eventer;
    $this->bundleInfo = $bundle_info;
    $this->hiddenTabPlacementStorage = $hidden_tab_placement_storage;
    $this->uuid = $uuid;
    $this->formBuilder = $form_builder;
    $this->creditStorage = $credit_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $container->get('user.permissions'),
      $container->get('event_dispatcher'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
        ->getStorage('hidden_tab_placement'),
      $container->get('uuid'),
      $container->get('form_builder'),
      $container->get('entity_type.manager')->getStorage('hidden_tab_credit')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $target_types = ['node' => $this->t('Node')];
    $target_type = 'node';

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\hidden_tab\Entity\HiddenTabPage::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Whether if the page is enabled or not.'),
      '#default_value' => $this->entity->isNew() ? TRUE : $this->entity->status(),
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->description(),
    ];

    $form['tab_uri'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Tab Uri'),
      '#description' => $this->t('The Uri from which the tab page is accessible.'),
      '#default_value' => $this->entity->tabUri(),
      '#machine_name' => [
        'exists' => '\Drupal\hidden_tab\Utility::uriExists',
        'error' => $this->t('The uri is already in use.'),
      ],
      '#maxlength' => 255,
    ];

    $form['secret_uri'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Secret Uri'),
      '#description' => $this->t('The Uri from which the secret tab page is accessible.'),
      '#default_value' => $this->entity->secretUri(),
      '#machine_name' => [
        'exists' => '\Drupal\hidden_tab\Utility::uriExists',
        'error' => $this->t('The uri is already in use.'),
      ],
      '#maxlength' => 255,
      '#required' => FALSE,
    ];

    $form['is_access_denied'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show access denied'),
      '#description' => $this->t('It is possible and sometimes recommended to simply display page not found instead of access denied. Yet you get a log entry in case of illegal access.'),
      '#default_value' => $this->entity->isAccessDenied(),
    ];

    $form['target_entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Target Entity Type'),
      '#description' => $this->t('On which entity type this page is attached.'),
      '#default_value' => $this->entity->targetEntityType() ?: $target_type,
      '#options' => $target_types,
    ];

    $form['target_entity_bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Target Entity Bundle'),
      '#description' => $this->t('On which bundle this page is attached.'),
      '#default_value' => $this->entity->targetEntityBundle(),
      '#options' => FUtility::nodeBundlesSelectList(TRUE),
    ];

    $form['tab_view_permission'] = [
      '#type' => 'select',
      '#title' => $this->t('Tab Permission'),
      '#description' => $this->t('The permission user must posses to access the page via tab.'),
      '#default_value' => $this->entity->tabViewPermission() === NULL ? self::TAB_PERMISSION_DEFAULT_PERMISSION : $this->entity->tabViewPermission(),
      '#options' => Utility::permissionOptions($this->userPermissionService->getPermissions()),
    ];

    $form['secret_uri_view_permission'] = [
      '#type' => 'select',
      '#title' => $this->t('Secret Uri Permission'),
      '#description' => $this->t('The permission user must posses to access the page via secret uri.'),
      '#default_value' => $this->entity->secretUriViewPermission() === NULL ? 'access content' : $this->entity->secretUriViewPermission(),
      '#options' => Utility::permissionOptions($this->userPermissionService->getPermissions()),
    ];

    $form['credit_check_order'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credit check order'),
      '#description' => $this->t('Page/Entity/User, allowed values (comma separated): @order', [
        '@order' => HiddenTabPageForm::DEFAULT_CREDIT_CHECK_ORDER,
      ]),
      '#default_value' => $this->entity->creditCheckOrder()
        ? $this->entity->creditCheckOrder()
        : HiddenTabPageForm::DEFAULT_CREDIT_CHECK_ORDER,
    ];

    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#description' => $this->t('The template used to render the page. Will define page regions.'),
      '#default_value' => $this->entity->template()
        ? $this->entity->template()
        : 'hidden_tab_two_column',
      '#options' => HiddenTabTemplatePluginManager::man()
        ->pluginsForSelectElement('general'),
    ];

    $this->previews($form);

    $d = $this->t('The inline twig template used to render the page. Overrides template property. Use [regions.reg_N] where N is from 0 to region_count, for placements. Please note that each region_N is an array containing rendered html.');
    $form['inline_template'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Inline Template'),
      '#description' => $d,
      '#default_value' => $this->entity->inlineTemplate(),
    ];

    $d = $this->t('How many regions in form of regions.region_N will be available in inline region, where N is the count set here.');
    $form['inline_template_region_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Inline Template Region Count'),
      '#description' => $d,
      '#default_value' => $this->entity->inlineTemplateRegionCount(),
      '#min' => 0,
    ];

    // ------------------------------------------------------------------------

    // Used for checks in save step.
    $form['old_template'] = [
      '#type' => 'value',
      '#value' => $this->entity->template(),
      '#default_value' => $this->entity->template(),
    ];

    // Used for checks in save step.
    $form['old_inline_template'] = [
      '#type' => 'value',
      '#value' => $this->entity->inlineTemplate(),
      '#default_value' => $this->entity->inlineTemplate(),
    ];

    // Used for checks in save step.
    $form['old_inline_template_region_count'] = [
      '#type' => 'value',
      '#value' => $this->entity->inlineTemplateRegionCount(),
      '#default_value' => $this->entity->inlineTemplateRegionCount(),
    ];

    // Used for checks in save step.
    $is_inline = $this->entity->inlineTemplate() || !$this->entity->template();
    $form['old_is_inline'] = [
      '#type' => 'value',
      '#value' => $is_inline,
      '#default_value' => $is_inline,
    ];

    // Used for checks in save step.
    $form['old_tab_uri'] = [
      '#type' => 'value',
      '#value' => $this->entity->tabUri(),
      '#default_value' => $this->entity->tabUri(),
    ];

    // Used for checks in save step.
    $form['was_new'] = [
      '#type' => 'value',
      '#value' => $this->entity->isNew(),
      '#default_value' => $this->entity->isNew(),
    ];

    // Give other modules opportunity to add stuff to the form.
    $this->eventer->dispatch(HiddenTabPageFormEvent::EVENT_NAME,
      $this->event($form, $form_state, HiddenTabPageFormEvent::PHASE_FORM));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Don't show just yet if not entered. Let other validation handle it.
    if ($form_state->getValue('tab_uri') &&
      $form_state->getValue('tab_uri') === $form_state->getValue('secret_uri')) {
      $form_state->setErrorByName('tab_uri', $this->t("Both Uris can't be same"));
      $form_state->setErrorByName('secret_uri', $this->t("Both Uris can't be same"));
    }
    // Give other modules opportunity to validate their added stuff.
    $this->eventer->dispatch(HiddenTabPageFormEvent::EVENT_NAME,
      $this->event($form, $form_state, HiddenTabPageFormEvent::PHASE_VALIDATE));
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $inline = $form_state->getValue('inline_template');
    $form_state->unsetValue('inline_template');
    $iv = $inline && is_array($inline) && isset($inline['value']) ? $inline['value'] : '';
    $form_state->setValue('inline_template', $iv);
    $input = $form_state->getUserInput();
    $input['inline_template'] = $iv;
    $form_state->setUserInput($input);
    $this->entity->set('inline_template', $iv);


    $result = parent::save($form, $form_state);

    $is_inline = $form_state->getValue('inline_template') || !$form_state->getValue('template');
    $inline_changed = $form_state->getValue('old_is_inline') != $is_inline;

    $red = TRUE;
    $was_new = $form_state->getValue('was_new');

    if (!$was_new && $inline_changed) {
      $this->resetRegion($form_state);
      $this->messenger()->addWarning($this->t(
        'You have changed the template type (inline <---> file), please revisit the layout page and order the komponents as necessary.'));
    }
    elseif (!$was_new && !$is_inline && ($form_state->getValue('old_template') !== $form_state->getValue('template'))) {
      $this->resetRegion($form_state);
      $this->messenger()->addWarning($this->t(
        'You have changed the template, please revisit the layout page and order the komponents as necessary.'));
    }
    elseif (!$was_new && $is_inline && $form_state->getValue('old_inline_template') !== $form_state->getValue('inline_template')) {
      $this->messenger()->addWarning($this->t(
        'You have changed the inline template, please revisit the layout page and order the komponents as necessary.'));
    }
    elseif ($is_inline && ((int) $form_state->getValue('old_inline_template_region_count'))
      > ((int) $form_state->getValue('inline_template_region_count'))) {
      // Check if those regions were empty anyways.
      $old_count = (int) $form_state->getValue('old_inline_template_region_count');
      $new_count = (int) $form_state->getValue('inline_template_region_count');
      $placements = Utility::placementsOfPage($this->hiddenTabPlacementStorage, $this->entity->id());
      $found = FALSE;
      for ($i = $new_count; $i < $old_count; $i++) {
        foreach ($placements as $placement) {
          $placement_i = explode('_', $placement->region())[1];
          if ($placement_i >= $new_count) {
            $found = TRUE;
            break;
          }
        }
      }
      if ($found) {
        $this->resetRegion($form_state);
        $this->messenger()->addWarning($this->t(
          'You have reduced the inline template region count, please revisit the layout page and order the komponents as necessary.'));
      }
      else {
        $red = FALSE;
      }
    }
    else {
      $red = FALSE;
    }

    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new Hidden Tab Page %label.', $message_args)
      : $this->t('Updated Hidden Tab Page %label.', $message_args);
    $this->messenger()->addStatus($message);

    if ($red) {
      $form_state->setRedirect(
        'entity.hidden_tab_page.layout_form',
        ['hidden_tab_page' => $this->entity->id()]
      );
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }

    // Give other modules opportunity to save their added stuff.
    $this->eventer->dispatch(HiddenTabPageFormEvent::EVENT_NAME,
      $this->event($form, $form_state, HiddenTabPageFormEvent::PHASE_SUBMIT));

    if (!$was_new && ($form_state->getValue('tab_uri') !== $form_state->getValue('old_tab_uri'))) {
      $this->messenger()
        ->addWarning($this->t("You have changed the Uri, don't forget to clear the caches for the Uri change to take effect."));
    }
    elseif ($was_new) {
      $this->messenger()
        ->addWarning($this->t("You have added a new page, don't forget to clear the caches for the Uri addition to take effect."));
    }

    if (Utility::checkRedirect()) {
      $form_state->setRedirectUrl(Utility::checkRedirect());
    }

    return $result;
  }

  /**
   * Put all komponents of page in the first region.
   *
   * When a new template is chosen for page, old komponent's region may become
   * invalid, and may no longer be accessible in layout form, as they are put
   * into the layout form by their region (they become hidden) so when the
   * template is changed for a page, all komponents are put in the first region
   * of the new template, and the user can re-order them in the new template
   * again.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of page being saved.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function resetRegion(FormStateInterface $form_state) {
    $region = '';
    foreach (HiddenTabTemplatePluginManager::regionsOfTemplate($form_state->getValue('template')) as $_region => $crap) {
      $region = $_region;
      break;
    }
    foreach (Utility::placementsOfPage($this->hiddenTabPlacementStorage, $this->entity->id()) as $placement) {
      $placement->set('region', $region);
      $placement->save();
    }
  }

  /**
   * Event constructor.
   *
   * @param array $form
   *   Event argument.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Event argument.
   * @param int $phase
   *   Event argument.
   *
   * @return \Drupal\hidden_tab\Event\HiddenTabPageFormEvent
   */
  private function event(array &$form,
                         FormStateInterface $form_state,
                         int $phase) {
    /** @noinspection PhpParamsInspection */
    return new HiddenTabPageFormEvent(
      $form,
      $form_state,
      $this->getEntity(),
      $phase
    );
  }

  /**
   * Populate template previews in admin form.
   *
   * @param $form
   *   The form.
   */
  private function previews(&$form) {
    // Show all of them at once.
    $previews = [];
    foreach (HiddenTabTemplatePluginManager::templatePreviewImages() as $label => $img_uri) {
      $previews[$label] = [
        '#weight' => -10,
        '#theme' => 'image',
        '#width' => 100,
        '#height' => 200,
        '#style_name' => 'medium',
        '#uri' => $GLOBALS['base_url'] . '/' . $img_uri,
      ];
    }
    $pid = 'hidden_tab_admin_templates_preview';
    $attach = [];
    if (HiddenTabTemplatePluginManager::man()->exists($pid)) {
      /** @noinspection PhpUndefinedMethodInspection */
      $attach = HiddenTabTemplatePluginManager::man()
        ->plugin('hidden_tab_admin_templates_preview')->attachLibrary();
    }
    else {
      $this->messenger()->addWarning($this->t('Plugin missing: @plug', [
        '@plug' => 'hidden_tab_admin_templates_preview',
      ]));
    }
    $form['previews'] = [
      '#attached' => $attach,
      '#theme' => 'hidden_tab_hidden_tab_admin_templates_preview',
      '#previews' => $previews,
    ];
  }

}
