<?php

namespace Drupal\domain_config_ui;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Domain Config UI manager.
 *
 * @todo Find better way to cache domain and language.
 */
class DomainConfigUIManager {
  use DependencySerializationTrait {
    __wakeup as defaultWakeup;
    __sleep as defaultSleep;
  }

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * A cached domain.
   *
   * @var string
   */
  protected $domain;

  /**
   * A cached language.
   *
   * @var string
   */
  protected $language;

  /**
   * Constructs DomainConfigUIManager object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * Get selected config name.
   *
   * @param string $name
   *   The config name.
   *
   * @return string
   *   The prefixed config name.
   */
  public function getSelectedConfigName($name) {
    if ($domain_id = $this->getSelectedDomainId()) {
      $prefix = "domain.config.{$domain_id}.";
      if ($langcode = $this->getSelectedLanguageId()) {
        $prefix .= "{$langcode}.";
      }
      return $prefix . $name;
    }
    return $name;
  }

  /**
   * Get the selected domain ID.
   *
   * @return string
   *   The selected domain from query string or session.
   */
  public function getSelectedDomainId() {
    if (isset($this->domain)) {
      return $this->domain;
    }
    $request = $this->requestStack->getCurrentRequest();
    if ($request->query->has('domain_config_ui_domain')) {
      $this->domain = $request->query->get('domain_config_ui_domain');
    }
    elseif (isset($_SESSION['domain_config_ui_domain'])) {
      $this->domain = $_SESSION['domain_config_ui_domain'];
    }
    else {
      $this->domain = '';
    }
    return $this->domain;
  }

  /**
   * Get the selected language ID.
   *
   * @return string
   *   The domain selected language.
   */
  public function getSelectedLanguageId() {
    if (isset($this->language)) {
      return $this->language;
    }
    $request = $this->requestStack->getCurrentRequest();
    if ($request->query->has('domain_config_ui_language')) {
      $this->language = $request->get('domain_config_ui_language');
    }
    elseif (isset($_SESSION['domain_config_ui_language'])) {
      $this->language = $_SESSION['domain_config_ui_language'];
    }
    else {
      $this->language = '';
    }
    return $this->language;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = $this->defaultSleep();
    // Do not serialize static cache.
    unset($vars['domain'], $vars['language']);
    return $vars;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    $this->defaultWakeup();
    // Initialize static cache.
    $this->domain = NULL;
    $this->language = NULL;
  }

}
