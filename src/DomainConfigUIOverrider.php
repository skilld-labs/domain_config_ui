<?php

namespace Drupal\domain_config_ui;

use Drupal\Core\Config\StorageInterface;
use Drupal\domain_config\DomainConfigOverrider;
use Drupal\domain_config_ui\Config\Config;

/**
 * Overrides in order to use our own Config class.
 *
 * @see \Drupal\language\Config\LanguageConfigFactoryOverride for ways
 * this might be improved.
 */
class DomainConfigUIOverrider extends DomainConfigOverrider {

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::createConfigObject()
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return new Config(
      $name,
      $this->storage,
      $this->eventDispatcher,
      $this->typedConfigManager
    );
  }

}
