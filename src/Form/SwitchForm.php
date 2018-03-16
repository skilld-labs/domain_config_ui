<?php

namespace Drupal\domain_config_ui\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainLoaderInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SwitchForm.
 *
 * @package Drupal\domain_config_ui
 */
class SwitchForm extends FormBase {
  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The domain loader.
   *
   * @var \Drupal\domain\DomainLoaderInterface
   */
  protected $domainLoader;

  /**
   * Class constructor.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The Domain negotiator.
   * @param \Drupal\domain\DomainLoaderInterface $domain_loader
   *   The domain loader.
   */
  public function __construct(DomainNegotiatorInterface $domain_negotiator, DomainLoaderInterface $domain_loader) {
    // Set the Domain negotiator.
    $this->domainNegotiator = $domain_negotiator;
    $this->domainLoader = $domain_loader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class with negotiator.
    return new static(
      $container->get('domain.negotiator'),
      $container->get('domain.loader')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_config_ui_switch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    // Only allow access to domain administrators.
    $form['#access'] = $this->currentUser()->hasPermission('administer domains');

    // Fill current request path.
    if ($request) {
      $path = $request->getPathInfo();
      $form['current_path'] = [
        '#type' => 'value',
        '#value' => $path,
      ];
    }

    // Add domain switch select field.
    if ($selected_domain = $this->domainNegotiator->getSelectedDomain()) {
      $selected = $selected_domain->id();
    }
    else {
      $selected = $form_state->getValue('config_save_domain');
    }
    $form['config_save_domain'] = [
      '#type' => 'select',
      '#title' => 'Save config for:',
      '#options' => array_merge(['' => 'All Domains'], $this->domainLoader->loadOptionsList()),
      '#default_value' => $selected,
      '#ajax' => ['callback' => '::switchCallback'],
    ];

    // Attach CSS to position form.
    $form['#attached']['library'][] = 'domain_config_ui/drupal.domain_config_ui.admin';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form does not require submit handler.
  }

  /**
   * Callback to remember save mode.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state array.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response.
   */
  public function switchCallback(array &$form, FormStateInterface $form_state) {
    $_SESSION['config_save_domain'] = $form_state->getValue('config_save_domain');
    $response = new AjaxResponse();
    // Refresh the page after changing the domain.
    $path = $form['current_path']['#value'];
    if ($path != '') {
      $url = Url::fromUri('base:' . $path);
      $url->setAbsolute();
      $response->addCommand(new RedirectCommand($url->toString()));
    }
    return $response;
  }

}
