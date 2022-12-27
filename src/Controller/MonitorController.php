<?php

namespace Drupal\monitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\monitor\Form\SettingsForm;
use Drupal\monitor\MonitorStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for monitor routes.
 */
class MonitorController extends ControllerBase {

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
   * Renders the monitor
   */
  public function build() {
    //check if we should read dummy data
    $data = match(boolval($this->config(SettingsForm::CONFIG)->get(SettingsForm::CONFIG_DUMMYDATA))) {
     true => json_decode(file_get_contents(\Drupal::service('extension.list.module')->getPath('monitor') . '/data/data.json')),
      // read from internal storage
      default => array_map(function ($project) {
        return $this->monitorStorage->getProjectData($project);
      }, $this->monitorStorage->getProjects())
    };

    return [
      '#theme' => 'monitor',
      'projects' => $data,
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
