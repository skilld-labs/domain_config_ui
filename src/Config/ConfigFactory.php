<?php
namespace Drupal\domain_config_ui\Config;

use Drupal\Core\Config\ConfigFactory as CoreConfigFactory;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\domain_config\DomainConfigOverrider;
use Drupal\domain_config_ui\Config\Config;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Overrides Drupal\Core\Config\ConfigFactory in order to use our own Config class.
 */
class ConfigFactory extends CoreConfigFactory {

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::doLoadMultiple()
   */
  protected function doLoadMultiple(array $names, $immutable = TRUE) {
    // Let parent load multiple load as usual.
    $list = parent::doLoadMultiple($names, $immutable);

    // Pre-load remaining configuration files.
    if (!$immutable && !empty($names)) {
      // Initialise override information.
      $module_overrides = array();
      $storage_data = $this->storage->readMultiple($names);

      // Load module overrides so that domain specific config is loaded in admin forms.
      if (!empty($storage_data)) {
        // Only get domain overrides if we have configuration to override.
        $module_overrides = $this->loadDomainOverrides($names);
      }

      foreach ($storage_data as $name => $data) {
        $cache_key = $this->getConfigCacheKey($name, $immutable);

        if (isset($module_overrides[$name])) {
          $this->cache[$cache_key]->setModuleOverride($module_overrides[$name]);
          $list[$name] = $this->cache[$cache_key];
        }

        $this->propagateConfigOverrideCacheability($cache_key, $name);
      }
    }

    return $list;
  }

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::doGet()
   */
  protected function doGet($name, $immutable = TRUE) {
    // Do not apply overrides if configuring 'all' domains or config is immutable.
    if (!isset($_SESSION['config_save_domain'])
      || empty($_SESSION['config_save_domain'])
      || $immutable) {
      return parent::doGet($name, $immutable);
    }

    if ($config = $this->doLoadMultiple([$name], $immutable)) {
      return $config[$name];
    }
    else {
      // If the configuration object does not exist in the configuration
      // storage, create a new object.
      $config = $this->createConfigObject($name, $immutable);

      // Load domain overrides so that domain specific config is loaded in admin forms.
      $overrides = $this->loadDomainOverrides(array($name));
      if (isset($overrides[$name])) {
        $config->setModuleOverride($overrides[$name]);
      }

      foreach ($this->configFactoryOverrides as $override) {
        $config->addCacheableDependency($override->getCacheableMetadata($name));
      }

      return $config;
    }
  }

/**
   * Get arbitrary overrides for the named configuration objects from Domain module.
   *
   * @param array $names
   *   The names of the configuration objects to get overrides for.
   *
   * @return array
   *   An array of overrides keyed by the configuration object name.
   */
  protected function loadDomainOverrides(array $names) {
    $overrides = array();
    foreach ($this->configFactoryOverrides as $override) {
      // Only return domain overrides.
      if ($override instanceof DomainConfigOverrider) {
        $overrides = $override->loadOverrides($names);
      }
    }
    return $overrides;
  }

}