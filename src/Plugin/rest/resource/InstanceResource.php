<?php

namespace Drupal\monitor\Plugin\rest\resource;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\monitor\LogManager;
use Drupal\monitor\MonitorStorage;
use Drupal\monitor\SendToMonitorFlag;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Represents InstanceResource records as resources.
 *
 * @RestResource (
 *   id = "monitor_instances",
 *   label = @Translation("InstanceResource"),
 *   uri_paths = {
 *     "create" = "/monitor/instance"
 *   }
 * )
 *
 */
class InstanceResource extends MonitorResource {
  protected MonitorStorage $monitorStorage;
  protected LogManager $logManager;
  protected QueueInterface $queue;


  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param LoggerInterface $logger
   * @param MonitorStorage $monitorStorage
   * @param LogManager $logManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, MonitorStorage $monitorStorage, LogManager $logManager, QueueFactory $queue) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->monitorStorage = $monitorStorage;
    $this->logManager = $logManager;
    $this->queue = $queue->get('monitor_queueworker', true);
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @return InstanceResource|ResourceBase|static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('rest'),
        $container->get('monitor.storage'),
        $container->get('monitor.log_manager'),
        $container->get('queue')
    );
  }

  /**
   * post request
   *
   * @param array|null $data
   * @return ModifiedResourceResponse
   * @throws TempStoreException
   */
  public function post(?array $data): ModifiedResourceResponse {
    if (!$data) {
      throw new UnprocessableEntityHttpException('No data provided');
    }

    $this->checkForProjectAndEnvironment($data);

    if ($this->logManager->dataFromAllowedEnvironment($data)) {
      $this->sendToQueue($data);
    }

    // Return the newly created record in the response body.
    return new ModifiedResourceResponse($data, 201);
  }

  /**
   * Update the storage
   *
   * @param array $data
   * @return void
   * @throws TempStoreException
   */
  private function sendToQueue(array $data): void {
    if(!$this->queue->createItem($data)) {
      \Drupal::logger('monitor')->error('Could not add item to queue. {itemData}', ['itemData' => json_encode($data), SendToMonitorFlag::SEND_TO_MONITOR_KEY->value => false]);

    }
  }
}
