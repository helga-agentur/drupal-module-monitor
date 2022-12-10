<?php

namespace Drupal\monitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\monitor\MonitorStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a monitor form.
 */
class StorageDeletionForm extends FormBase {

  protected MonitorStorage $monitorStorage;

  public function __construct(MonitorStorage $monitorStorage) {
    $this->monitorStorage = $monitorStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('monitor.storage')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'monitor_storage_deletion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['project'] = [
      '#type' => 'select',
      '#title' => $this->t('Project'),
      '#description' => $this->t('In case you want to delete the whole project, choose "All" for the environment'),
      '#required' => TRUE,
      '#options' => $this->monitorStorage->getProjects(),
      '#ajax' => [
        'callback' => '::updateEnvironments',
        'event' => 'change',
        'wrapper' => 'environment',
      ]
    ];

    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#required' => TRUE,
      '#prefix' => '<div id="environment">',
      '#suffix' => '</div>',
    ];
    $project = $form['project']['#options'][$form_state->getValue('project')];
    if ($project) {
      $form['environment']['#options'] = $this->monitorStorage->getEnvironments($project);
      $form['environment']['#options'][] = 'All';
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];

    return $form;
  }

  public function updateEnvironments(array &$form, FormStateInterface $form_state) {
    $project = $form['project']['#options'][$form_state->getValue('project')];
    $form['environment']['#options'] = $this->monitorStorage->getEnvironments($project);
    $form['environment']['#options'][] = 'All';
    return $form['environment'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $project = $form['project']['#options'][$form_state->getValue('project')];
    $environment = $form['environment']['#options'][$form_state->getValue('environment')];
    if ($project && $environment) {
      //depending on the value of environment, delete whole project or environment only.
      if ($environment == 'All') {
        $this->monitorStorage->deleteProject($project);
      } else {
        $this->monitorStorage->deleteInstanceData($project, $environment);
      }

      $this->messenger()->addStatus($this->t('The environment was deleted'));
    } else {
      $this->messenger()->addError($this->t('The environment could not get deleted.'));
    }
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
}
