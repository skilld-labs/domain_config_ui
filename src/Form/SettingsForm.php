<?php

namespace Drupal\domain_config_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_config_ui_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['domain_config_ui.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('domain_config_ui.settings');
    $form['allowed'] = array(
      '#type' => 'textarea',
      '#rows' => 5,
      '#columns' => 40,
      '#title' => $this->t('Include Domain Config UI switcher in the following pages'),
      '#default_value' => $config->get('allowed', "/admin/appearance\r\n/admin/config/system/site-information"),
      '#description' => $this->t("Enter any paths where you wan't the Domain Config UI switcher displayed. One path per line."),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('domain_config_ui.settings')
      ->set('allowed', $form_state->getValue('allowed'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
