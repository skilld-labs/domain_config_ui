<?php

namespace Drupal\domain_config_ui;

use Drupal\domain\DomainLoaderInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Domain Config UI manager.
 */
class DomainConfigUIManager {
  /**
   * A storage controller instance for reading and writing configuration data.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * Domain loader.
   *
   * @var \Drupal\domain\DomainLoaderInterface
   */
  protected $domainLoader;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The domain context of the request.
   *
   * @var \Drupal\domain\DomainInterface
   */
  protected $domain;

  /**
   * The language context of the request.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $language;

  /**
   * Constructs domain config UI service.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The configuration storage engine.
   * @param \Drupal\domain\DomainLoaderInterface $domain_loader
   *   The domain loader.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(StorageInterface $storage, DomainLoaderInterface $domain_loader, LanguageManagerInterface $language_manager) {
    $this->storage = $storage;
    $this->domainLoader = $domain_loader;
    $this->languageManager = $language_manager;

    // Get the language context.
    if ($language = $this->getSelectedLanguage()) {
      $this->language = $language;
    }

    // Get the domain context.
    if ($domain = $this->getSelectedDomain()) {
      $this->domain = $domain;
    }
  }

  /**
   * Load only overrides for selected domain and language.
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (!empty($this->domain)) {
      foreach ($names as $name) {
        $config_name = $this->getSelectedConfigName($name);
        if ($override = $this->storage->read($config_name)) {
          $overrides[$name] = $override;
        }
      }
    }
    return $overrides;
  }

  /**
   * Get selected config name.
   *
   * @param string $name
   *   The config name.
   */
  public function getSelectedConfigName($name) {
    // Build prefix and add to front of existing key.
    if ($selected_domain = $this->getSelectedDomain()) {
      $prefix = 'domain.config.' . $selected_domain->id() . '.';
      // Add selected language.
      if ($language = $this->getSelectedLanguage()) {
        $prefix .= $language->getId() . '.';
      }
      $name = $prefix . $name;
    }
    return $name;
  }

  /**
   * Get the selected domain.
   */
  public function getSelectedDomain() {
    $selected_domain_id = $this->getSelectedDomainId();
    if ($selected_domain_id && $selected_domain = $this->domainLoader->load($selected_domain_id)) {
      return $selected_domain;
    }
  }

  /**
   * Get the selected domain ID.
   */
  public function getSelectedDomainId() {
    return !empty($_SESSION['domain_config_ui']['config_save_domain']) ? $_SESSION['domain_config_ui']['config_save_domain'] : '';
  }

  /**
   * Set the current selected domain ID.
   *
   * @param string $domain_id
   *   The Domain ID.
   */
  public function setSelectedDomain($domain_id) {
    if ($domain = $this->domainLoader->load($domain_id)) {
      // Set session for subsequent request.
      $_SESSION['domain_config_ui']['config_save_domain'] = $domain_id;
      // Switch active language to load selected domain config immediately.
      // This is primarily for switching domain with AJAX request.
      $this->domain = $domain;
    }
    else {
      $_SESSION['domain_config_ui']['config_save_domain'] = '';
      unset($this->domain);
    }
  }

  /**
   * Set the selected language.
   *
   * @param string $language_id
   *   The language ID.
   */
  public function setSelectedLanguage($language_id) {
    if ($language = $this->languageManager->getLanguage($language_id)) {
      // Set session for subsequent request.
      $_SESSION['domain_config_ui']['config_save_language'] = $language_id;
      // Switch active language to load selected domain config immediately.
      // This is primarily for switching domain with AJAX request.
      $this->language = $language;
    }
    else {
      $_SESSION['domain_config_ui']['config_save_language'] = '';
      unset($this->language);
    }
  }

  /**
   * Get the selected language ID.
   */
  public function getSelectedLanguageId() {
    return !empty($_SESSION['domain_config_ui']['config_save_language']) ? $_SESSION['domain_config_ui']['config_save_language'] : '';
  }

  /**
   * Get the selected language.
   */
  public function getSelectedLanguage() {
    $selected_language_id = $this->getSelectedLanguageId();
    if ($selected_language_id && $selected_language = $this->languageManager->getLanguage($selected_language_id)) {
      return $selected_language;
    }
  }

}
