<?php

namespace Drupal\monitor\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\monitor\MonitorStorage;
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
class InstanceResource extends ResourceBase implements DependentPluginInterface {

  protected MonitorStorage $monitorStorage;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param LoggerInterface $logger
   * @param MonitorStorage $monitorStorage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, MonitorStorage $monitorStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->monitorStorage = $monitorStorage;
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
    );
  }

  /**
   * post request
   *
   * @param $data
   * @return ModifiedResourceResponse
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function post($data): ModifiedResourceResponse {
    $this->validate($data);
    $this->update($data);

    // Return the newly created record in the response body.
    return new ModifiedResourceResponse($data, 201);
  }

  /**
   * Validates the data before processing
   *
   * @param $data
   * @return void
   */
  private function validate($data): void {
    if(!key_exists('id', $data)) throw new UnprocessableEntityHttpException('Provide at least an id.');
  }

  /**
   * Update the storage
   *
   * @param $data
   * @return void
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function update($data): void {
    $this->monitorStorage->setInstanceData($data['id'], $data['data']);
  }

  public function calculateDependencies() {
    // TODO: Implement calculateDependencies() method.
  }

  /**
   * {@inheritdoc}
   */
  public function permissions() {
    return [];
  }
}
