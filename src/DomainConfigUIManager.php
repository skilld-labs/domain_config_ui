<?php

namespace Drupal\domain_config_ui;

/**
 * Domain Config UI manager.
 */
class DomainConfigUIManager {

  /**
   * Get selected config name.
   *
   * @param string $name
   *   The config name.
   */
  public function getSelectedConfigName($name) {
    // Build prefix and add to front of existing key.
    $prefix = 'domain.config.' . $this->getSelectedDomainId() . '.';
    // Add selected language.
    $prefix .= $this->getSelectedLanguageId() . '.';
    $name = $prefix . $name;
    return $name;
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
  public function setSelectedDomainId($domain_id) {
    // Set session for subsequent request.
    $_SESSION['domain_config_ui']['config_save_domain'] = $domain_id;
  }


  /**
   * Get the selected language ID.
   */
  public function getSelectedLanguageId() {
    return !empty($_SESSION['domain_config_ui']['config_save_language']) ? $_SESSION['domain_config_ui']['config_save_language'] : '';
  }

  /**
   * Set the selected language.
   *
   * @param string $language_id
   *   The language ID.
   */
  public function setSelectedLanguageId($language_id) {
    // Set session for subsequent request.
    $_SESSION['domain_config_ui']['config_save_language'] = $language_id;
  }


}
