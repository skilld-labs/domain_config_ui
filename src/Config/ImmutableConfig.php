<?php

namespace Drupal\domain_config_ui\Config;

use Drupal\Core\Config\ImmutableConfig as CoreImmutableConfig;
use Drupal\domain_config_ui\DomainConfigUIManager;

/**
 * Extend core Config class to load domain specific configuration.
 */
class ImmutableConfig extends CoreImmutableConfig {

  /**
   * The Domain config UI manager.
   *
   * @var \Drupal\domain_config_ui\DomainConfigUIManager
   */
  protected $domainConfigUIManager;

  /**
   * Set the Domain config UI manager.
   *
   * @param \Drupal\domain_config_ui\DomainConfigUIManager $domain_config_ui_manager
   *   The Domain config UI manager.
   */
  public function setDomainConfigUiManager(DomainConfigUIManager $domain_config_ui_manager) {
    $this->domainConfigUIManager = $domain_config_ui_manager;
  }

}
