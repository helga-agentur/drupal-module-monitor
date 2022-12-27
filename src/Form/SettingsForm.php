<?php

namespace Drupal\monitor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure monitor settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  const CONFIG = 'monitor.settings';
  const CONFIG_DUMMYDATA = 'useDummyData';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'monitor_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[self::CONFIG_DUMMYDATA] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use dummy data'),
      '#description' => 'Sometimes it can be useful to use dummy data. Especially when you are developing locally.',
      '#default_value' => $this->config(self::CONFIG)->get(self::CONFIG_DUMMYDATA),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(self::CONFIG)
      ->set(self::CONFIG_DUMMYDATA, $form_state->getValue(self::CONFIG_DUMMYDATA))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
