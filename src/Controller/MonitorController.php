<?php

namespace Drupal\monitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\example\ExampleInterface;
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
    $projects = array_map(function ($project) {
      return $this->monitorStorage->getProjectData($project);
    }, $this->monitorStorage->getProjects());

    return [
      '#theme' => 'monitor',
      'projects' => $projects,
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
