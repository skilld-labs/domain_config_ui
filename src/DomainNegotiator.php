<?php

namespace Drupal\domain_config_ui;

use Drupal\domain\DomainNegotiator as Negotiator;

/**
 * {@inheritdoc}
 */
class DomainNegotiator extends Negotiator {

  /**
   * Determine the active domain.
   */
  protected function negotiateActiveDomain() {
    // Set http host to be that of the selected domain to configure.
    if ($selected_domain = $this->getSelectedDomain()) {
      $httpHost = $selected_domain->getHostname();
    }
    else {
      $httpHost = $this->negotiateActiveHostname();
    }
    $this->setRequestDomain($httpHost);
    return $this->domain;
  }

  /**
   * Get the selected domain.
   */
  public function getSelectedDomain() {
    $selected_domain_id = $this->getSelectedDomainId();
    if ($selected_domain_id
      && $selected_domain = $this->domainStorage->load($selected_domain_id)) {
      return $selected_domain;
    }
  }

  /**
   * Get the selected domain ID.
   */
  public function getSelectedDomainId() {
    // Return selected domain ID on admin paths only.
    return !empty($_SESSION['config_save_domain']) ?
      $_SESSION['config_save_domain'] : '';
  }

}
