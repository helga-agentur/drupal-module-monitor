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
    $instances = $this->monitorStorage->getInstances();
    $data = array_map(function ($instanceIdentifier) {
      $data = $this->monitorStorage->getInstanceData($instanceIdentifier);
      $data['identifier'] = $instanceIdentifier;
      return $data;
    }, $instances);

    return [
      '#theme' => 'monitor',
      'instances' => $data,
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
