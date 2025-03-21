<?php

namespace Drupal\monitor\Plugin\rest\resource;

use Drupal\monitor\LogManager;
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents LogResource records as resources.
 *
 * @RestResource (
 *   id = "monitor_logs",
 *   label = @Translation("LogResource"),
 *   uri_paths = {
 *     "create" = "/monitor/log"
 *   }
 * )
 *
 */
class LogResource extends MonitorResource {

  private LogManager $logManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, LogManager $logManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->logManager = $logManager;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @return LogResource
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): LogResource {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('rest'),
        $container->get('monitor.log_manager'),
    );
  }

  /**
   * post request
   *
   * @param $data
   * @return ModifiedResourceResponse
   */
  public function post($data): ModifiedResourceResponse {
    $this->checkForProjectAndEnvironment($data);

    // Process and filter log through LogManager
    $this->logManager->processLog($data);

    return new ModifiedResourceResponse($data, 201);
  }
}
