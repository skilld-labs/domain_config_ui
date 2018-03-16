<?php

namespace Drupal\domain_config_ui\Config;

use Drupal\Core\Config\Config as CoreConfig;

/**
 * Extends core Config class to save domain specific configuration.
 */
class Config extends CoreConfig {
  /**
   * List of config that should always be saved globally. Use * for wildcards.
   *
   * @var array
   */
  protected $disallowedConfig = [
    'core.extension',
    'domain.record.*',
    'domain_alias.*',
  ];

  /**
   * {@inheritdoc}
   */
  public function save($has_trusted_data = FALSE) {
    // Remember original config name.
    $originalName = $this->name;

    try {
      // Get domain config name for saving.
      $domainConfigName = $this->getDomainConfigName();

      // If config is new and we are saving domain specific configuration,
      // save with original name so there is always a default configuration.
      if ($this->isNew && $domainConfigName != $originalName) {
        parent::save($has_trusted_data);
      }

      // Switch to use domain config name and save.
      $this->name = $domainConfigName;
      parent::save($has_trusted_data);
    }
    catch (\Exception $e) {
      // Reset back to original config name if save fails and re-throw.
      $this->name = $originalName;
      throw $e;
    }

    // Reset back to original config name after saving.
    $this->name = $originalName;

    return $this;
  }

  /**
   * Get the domain config name.
   */
  protected function getDomainConfigName() {
    $disallowed = $this->disallowedConfig;
    // Get default disallowed config and allow other modules to alter.
    \Drupal::moduleHandler()->alter('domain_config_disallowed', $disallowed);

    // Return original name if reserved as global configuration.
    foreach ($disallowed as $config_name) {
      // Convert config_name into into regex.
      // Escapes regex syntax, but keeps * wildcards.
      $pattern = '/^' . str_replace('\*', '.*', preg_quote($config_name, '/')) . '$/';
      if (preg_match($pattern, $this->name)) {
        return $this->name;
      }
    }

    // Build prefix and add to front of existing key.
    $domain_id = !empty($_SESSION['config_save_domain']) ?
      $_SESSION['config_save_domain'] : '';
    if ($domain = \Drupal::entityTypeManager()
      ->getStorage('domain')
      ->load($domain_id)
    ) {
      $prefix = 'domain.config.' . $domain->id() . '.';
      // @TODO: Allow selection of language.
      if ($language = \Drupal::languageManager()->getCurrentLanguage()) {
        $prefix .= $language->getId() . '.';
      }
      return $prefix . $this->name;
    }

    // Return current name by default.
    return $this->name;
  }

}
