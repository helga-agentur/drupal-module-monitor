<?php

namespace Drupal\monitor\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\monitor\MonitorStorage;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Base class for Monitor resources.
 */
class MonitorResource extends ResourceBase implements DependentPluginInterface {

  const _IDENTIFIER = 'project';
  const _ENVIRONMENT = 'environment';

  protected MonitorStorage $monitorStorage;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param LoggerInterface $logger
   * @param MonitorStorage $monitorStorage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
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
    );
  }

  /**
   * Validates the data before processing
   *
   * @param $data
   * @return void
   */
  protected function validate($data): void {
    if(!key_exists(self::_IDENTIFIER, $data) || !key_exists(self::_ENVIRONMENT, $data)) throw new UnprocessableEntityHttpException('Provide at least a project and its environment.');
  }

  public function calculateDependencies() {}
}
