<?php

namespace Drupal\domain_config_ui;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\StorageInterface;
use Drupal\domain_config\DomainConfigOverrider;

/**
 * Domain UI-specific config overrides.
 */
class DomainConfigUIOverrider extends DomainConfigOverrider {

  /**
   * The Domain config UI manager.
   *
   * @var Drupal\domain_config_ui\DomainConfigUIManager
   */
  protected $domainConfigUIManager;

  /**
   * Constructs a DomainConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The configuration storage engine.
   * @param \Drupal\domain_config_ui\DomainConfigUIManager $domain_config_ui_manager
   *   The domain config UI manager.
   */
  public function __construct(StorageInterface $storage, DomainConfigUIManager $domain_config_ui_manager) {
    $this->storage = $storage;
    $this->domainConfigUIManager = $domain_config_ui_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    // loadOverrides() runs on config entities, which means that if we try
    // to run this routine on our own data, then we end up in an infinite loop.
    // So ensure that we are _not_ looking up a domain.record.*.
    $check = current($names);
    $list = explode('.', $check);
    if (isset($list[0]) && isset($list[1]) && $list[0] == 'domain' && $list[1] == 'record') {
      return $overrides;
    }

    foreach ($names as $name) {
      if ($config_name = $this->getDomainConfigUiName($name)) {
        // Check to see if the config storage has an appropriately named file
        // containing override data.
        if ($override = $this->storage->read($config_name['langcode'])) {
          $overrides[$name] = $override;
        }
        // Check to see if we have a file without a specific language.
        elseif ($override = $this->storage->read($config_name['domain'])) {
          $overrides[$name] = $override;
        }

        // Apply any settings.php overrides.
        if (isset($GLOBALS['config'][$config_name['langcode']])) {
          $overrides[$name] = $GLOBALS['config'][$config_name['langcode']];
        }
        elseif (isset($GLOBALS['config'][$config_name['domain']])) {
          $overrides[$name] = $GLOBALS['config'][$config_name['domain']];
        }
      }
    }
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDomainConfigUiName($name) {
    if ($domain_id = $this->domainConfigUIManager->getSelectedDomainId()) {
      $language_id = $this->domainConfigUIManager->getSelectedLanguageId();
      return [
        'langcode' => $language_id ? "domain.config.{$domain_id}.{$language_id}.{$name}"
        : "domain.config.{$domain_id}.{$name}",
        'domain' => "domain.config.{$domain_id}.{$name}",
      ];
    }
    elseif (!empty($this->domain)) {
      $domain_id = $this->domain->id();
      $language_id = $this->language->getId();
      return [
        'langcode' => $language_id ? "domain.config.{$domain_id}.{$language_id}.{$name}"
        : "domain.config.{$domain_id}.{$name}",
        'domain' => "domain.config.{$domain_id}.{$name}",
      ];
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    $suffix = $this->domainConfigUIManager->getSelectedDomainId() ?: '';
    $suffix .= $this->domainConfigUIManager->getSelectedLanguageId() ?: '';
    return ($suffix) ? $suffix : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    if (empty($this->contextSet)) {
      $this->initiateContext();
    }
    $metadata = new CacheableMetadata();
    if (!empty($this->domain)) {
      $metadata->addCacheContexts(['url.site', 'languages:language_interface']);
    }
    return $metadata;
  }

}
