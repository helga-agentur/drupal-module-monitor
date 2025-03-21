<?php

namespace Drupal\monitor\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\monitor\MonitorStorage;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Base class for Monitor resources.
 */
class MonitorResource extends ResourceBase implements DependentPluginInterface {

  const IDENTIFIER = 'project';
  const ENVIRONMENT = 'environment';
  const ALLOWED_ENVIRONMENTS = ['stage', 'integration', 'live'];

  protected MonitorStorage $monitorStorage;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param LoggerInterface $logger
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @return MonitorResource|ResourceBase|static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('rest'),
        $container->get('queue')
    );
  }

  /**
   * Validates the data before processing
   *
   * @param $data
   * @return void
   */
  protected function checkForProjectAndEnvironment($data): void {
    if (!isset($data[self::IDENTIFIER], $data[self::ENVIRONMENT])) {
      throw new UnprocessableEntityHttpException('Provide at least a project and its environment.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {}
}
