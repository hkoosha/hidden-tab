<?php

namespace Drupal\hidden_tab;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Drupal\hidden_tab\Entity\HiddenTabCreditInterface;
use Drupal\hidden_tab\Entity\HiddenTabMailerInterface;
use Drupal\hidden_tab\Entity\HiddenTabPageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * TODO make this an injectable service.
 *
 * @package Drupal\hidden_tab
 */
final class Utility {

  /**
   * Do not allow instantiation of utility class.
   */
  private function __construct() {
  }

  public const TICK = '✔'; // '&#x2714;';

  public const CROSS = '✘'; // '&#10008;';

  public const WARNING = '⚠️'; // '&#9888;';

  public const QUERY_NAME = 'hash';

  /**
   *  Unicode HTML element, tick or cross based on boolean evaluation of $eval.
   *
   * @param $eval
   *   Parameter to evaluate as boolean.
   *
   * @return string
   *   Unicode HTML element, tick or cross based on boolean evaluation of $eval.
   */
  public static function mark($eval): string {
    return !!$eval ? static::TICK : static::CROSS;
  }

  // ============================================================== PAGE ENTITY

  /**
   * Check a Uri and see if it contains a page and find that page's entity.
   *
   * @param string $tab_uri
   *   Uri to check.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPageInterface|null
   *   Found page, if any.
   */
  public static function pageByTabUri($tab_uri): ?HiddenTabPageInterface {
    $page = \Drupal::entityTypeManager()
      ->getStorage('hidden_tab_page')
      ->loadByProperties(['tab_uri' => $tab_uri]);
    if ($page && is_array($page) && count($page)) {
      foreach ($page as $p) {
        return $p;
      }
    }
    return NULL;
  }

  /**
   * Check a Uri and see if it contains a page and find that page's entity.
   *
   * @param string $secret_uri
   *   Uri to check.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPageInterface|null
   *   Found page, if any.
   */
  public static function pageBySecretUri($secret_uri): ?HiddenTabPageInterface {
    $page = \Drupal::entityTypeManager()
      ->getStorage('hidden_tab_page')
      ->loadByProperties(['secret_uri' => $secret_uri]);
    if ($page && is_array($page) && count($page)) {
      foreach ($page as $p) {
        return $p;
      }
    }
    return NULL;
  }

  /**
   * Load a page by it's id.
   *
   * @param string $id
   *   Page id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPageInterface|null
   *   Loaded page, if any.
   */
  public static function page(string $id): ?HiddenTabPageInterface {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page */
    $page = \Drupal::entityTypeManager()
      ->getStorage('hidden_tab_page')
      ->load($id);
    return $page;
  }

  /**
   * Load all pages.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPageInterface[]
   *   All pages loaded.
   */
  public static function pages(): array {
    /** @var \Drupal\hidden_tab\Entity\HiddenTabPageInterface[] $pages */
    $pages = \Drupal::entityTypeManager()
      ->getStorage('hidden_tab_page')
      ->loadMultiple();
    return $pages;
  }

  /**
   * Id to label array of all pages suitable for select element options.
   *
   * @param array $pages
   *   All the page entities.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPageInterface[]
   *   Id to label array of all pages suitable for select element options.
   */
  public static function allPagesForSelectElement(array $pages): array {
    $options = [];
    foreach ($pages as $page) {
      $options[$page->id()] = $page->label();
    }
    return $options;
  }

  /**
   * Check to see if a Uri already exists or not.
   *
   * Machine name element callback.
   *
   * TODO FIXME check all drupal Uris.
   *
   * @param string $uri
   *   Uri to check.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return bool
   *   True if is, false otherwise.
   */
  public static function uriExists(string $uri): bool {
    $storage = \Drupal::entityTypeManager()
      ->getStorage('hidden_tab_page');
    $page = $storage->loadByProperties(['tab_uri' => $uri]);
    if ($page && is_array($page) && count($page)) {
      return TRUE;
    }
    $page = $storage->loadByProperties(['secret_uri' => $uri]);
    if ($page && is_array($page) && count($page)) {
      return TRUE;
    }
    return FALSE;
  }

  // ================================================================ PLACEMENT

  /**
   * Given a page, load all it's placements.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The storage to load placements.
   * @param string $page_id
   *   The page in question.
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabPlacementInterface[]
   *   All the placements in the page.
   */
  public static function placementsOfPage(EntityStorageInterface $storage,
                                          string $page_id): array {
    return $storage->loadByProperties(['target_hidden_tab_page' => $page_id]);
  }

  // ==================================================================== STUFF

  /**
   * All permission in the drupal installation, id to label array.
   *
   * @param array $permissions
   *   Array of permissions.
   * @param bool $none_option
   *   Should the list include a none options or not.
   *
   * @return array
   *   All permission in the drupal installation, id to label array.
   */
  public static function permissionOptions(array $permissions = NULL,
                                           $none_option = TRUE): array {
    if ($permissions === NULL) {
      $permissions = \Drupal::service('user.permissions')->getPermissions();
    }
    $none = $none_option ? [
      '' => \Drupal::translation()
        ->translate('None'),
    ] : [];
    $options = [];
    foreach ($permissions as $id => $info) {
      $t = str_replace('</em>', '', str_replace('<em class="placeholder">', '', $info['title']));
      $options[$id] = $t . ' (' . $info['provider'] . ')';
    }
    asort($options);
    return $none + $options;
  }

  /**
   * Calculate hash by key and data. Implementation is arbitrary.
   *
   * @param mixed $data
   *   The data.
   * @param mixed $key
   *   The hash key.
   *
   * @return string
   *   Calculated hash.
   */
  public static function hash($data, $key): string {
    return Crypt::hmacBase64(strval($data), strval($key));
  }

  public static function matches(HiddenTabCreditInterface $hash_entity,
                                 EntityInterface $entity,
                                 string $hash) {
    return static::hash($entity->id(), $hash_entity->secretKey()) === $hash;
  }

  // ===================================================================== MAIL


  /**
   * Current path good for lredirect value, to redirect back here later.
   *
   * Value lredirect is a normal path but slashes replaced with stars.
   *
   * @return string
   *   Current path good for lredirect value, to redirect back here later.
   *
   * @see \Drupal\hidden_tab\Utility::redirectThere()
   */
  public static function redirectHere(): string {
    $cr = \Drupal::service('request_stack')->getCurrentRequest();
    /** @noinspection PhpUndefinedMethodInspection */
    $rep = $cr->getSchemeAndHttpHost() . $cr->getRequestUri();
    return str_replace('/', '*', $rep);
  }

  /**
   * Get the lredirect value from the query or empty (that is, a single start).
   *
   * @return string
   *   Get the lredirect value from the query or empty (that is, a single
   *   start).
   *
   * @see \Drupal\hidden_tab\Utility::redirectHere()
   */
  public static function lRedirect(): string {
    return \Drupal::request()->query->get('lredirect') ?: '*';
  }

  /**
   * Find a suitable lredirect value created by redirectHere().
   *
   * @return \Drupal\Core\Url|null
   *   The found Url to redirect to.
   *
   * @see \Drupal\hidden_tab\Utility::redirectHere()
   */
  public static function checkRedirect(): ?Url {
    $q = static::lRedirect();
    $p = !$q || $q === '*' ? NULL : str_replace('*', '/', $q);
    if (!$p) {
      return NULL;
    }
    return Url::fromUri($p, [
      'lredirect' => $p,
    ]);
  }

  /**
   * Same path just like redirectHere() but for the given page.
   *
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page to redirect to.
   * @param \Drupal\hidden_tab\Entity\HiddenTabCreditInterface $credit
   *   The current hash being visited.
   *
   * @return string
   *   Same path just like redirectHere() but for the given page.
   *
   * @see \Drupal\hidden_tab\Utility::redirectHere()
   */
  public static function redirectThere(HiddenTabPageInterface $page,
                                       HiddenTabCreditInterface $credit): string {


    // TODO limited support of entity type.
    try {
      try {
        $target = $credit->targetEntity();
        if (!$target
          && $credit->targetEntityType()
          && \Drupal::routeMatch()->getParameter($credit->targetEntityType())) {
          $target = \Drupal::routeMatch()
            ->getParameter($credit->targetEntityType());
        }
        if (!$target) {
          $target = \Drupal::routeMatch()->getParameter('node');
        }
        if (!$target) {
          \Drupal::logger('hidden_tab')
            ->warning('could not find redirect target page={page} credit={credit}', [
              'page' => $page->id(),
              'credit' => $credit->id(),
            ]);
          return '*';
        }
        $uri = \Drupal::request()
            ->getSchemeAndHttpHost() . '/' . $target->getEntityTypeId()
          . '/' . $target->id() . '/' . $page->id();
        return str_replace('/', '*', $uri);
      }
      catch (\Throwable $error0) {
        \Drupal::logger('hidden_tab')
          ->warning('error when finding redirect page={page} credit={credit} msg={msg} trace={trace}', [
            'page' => $page->id(),
            'credit' => $credit->id(),
            'msg' => $error0->getMessage(),
            'trace' => $error0->getTraceAsString(),
          ]);
        return '*';
      }
    }
    catch (\Throwable $error1) {
      // Don't fail for dumb shit.
      \Drupal::logger('hidden_tab')
        ->error('error while erroring msg={msg}', [
          'msg' => $error1->getMessage(),
        ]);
      return '*';
    }
  }

  /**
   * Send an email (the secret link).
   *
   * @param string $mail
   *   The email address.
   * @param \Drupal\hidden_tab\Entity\HiddenTabPageInterface $page
   *   The page in question.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity in question.
   * @param \Drupal\hidden_tab\Entity\HiddenTabMailerInterface $mailer
   *   The mail configuration for the given page.
   *
   * @return bool
   *   True if success.
   */
  public static function email(string $mail,
                               HiddenTabPageInterface $page,
                               EntityInterface $entity,
                               HiddenTabMailerInterface $mailer): bool {
    $ok = \Drupal::service('plugin.manager.mail')->mail(
      'hidden_tab_mailer',
      'hidden_tab_mailer',
      $mail,
      // Lang will be handled by hidden_tab_mail() and $params.
      'en',
      [
        'page' => $page,
        'entity' => $entity,
        'mailer' => $mailer,
        // TODO find email's langcode (search in users) or fallback to site's
        // default.
        'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      ],
      NULL,
      TRUE
    );
    return $ok['result'] ? TRUE : FALSE;
  }

  /**
   * Find all mail configurations for a page.
   *
   * @param string $page_id
   *   The page in question.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\hidden_tab\Entity\HiddenTabMailerInterface[]
   *   Mail configurations set for a pge
   */
  public static function mailConfigOfPage(string $page_id): array {
    return \Drupal::entityTypeManager()->getStorage('hidden_tab_mailer')
      ->loadByProperties([
        'hidden_tab_page' => $page_id,
      ]);
  }

  /**
   * Log exceptions when working with an entity type on non-critical situations.
   *
   * @param \Throwable $error
   *   The occurred exception.
   * @param string $type
   *   The entity type who we were building fields for, and the exception
   *   occurred then.
   * @param null $prop
   *   Property being rendered while error happened.
   */
  public static function log(\Throwable $error, string $type, $prop = NULL, $entity_id = NULL) {
    \Drupal::logger('hidden_tab')
      ->error('exception when rendering entity, entity-type={type} property={prop}, id={id} message={msg} trace={trace}', [
        'type' => $type,
        'msg' => $error->getMessage(),
        'trace' => $error->getTraceAsString(),
        'prop' => $prop ?: '?',
        'id' => $entity_id ?: '?',
      ]);
  }

  public static function sayError(?\Throwable $error) {
    if (\Drupal::currentUser()
      ->hasPermission('administer site configuration')) {
      \Drupal::messenger()->addError($error ? $error->getMessage() : '?');
    }
  }

  /**
   * Calculates current Url, doh!
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   To calculate current Url from.
   *
   * @return string
   *   Current Url, doh!
   */
  public static function currentUrl(RequestStack $request_stack): string {
    $cr = $request_stack->getCurrentRequest();
    return $cr->getSchemeAndHttpHost() . $cr->getRequestUri();
  }

}
