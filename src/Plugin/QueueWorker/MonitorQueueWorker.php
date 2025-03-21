<?php

declare(strict_types=1);

namespace Drupal\monitor\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\monitor\LogManager;
use Drupal\monitor\MonitorStorage;
use Drupal\monitor\Plugin\rest\resource\MonitorResource;
use Drupal\monitor\SendToMonitorFlag;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'monitor_queueworker' queue worker.
 */
#[QueueWorker(
  id: 'monitor_queueworker',
  title: new TranslatableMarkup('QueueWorker'),
  cron: ['time' => 60],
)]
class MonitorQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected MonitorStorage $monitorStorage;
  protected LogManager $logManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MonitorStorage $monitorStorage, LogManager $logManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->monitorStorage = $monitorStorage;
    $this->logManager = $logManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    try {
      $this->monitorStorage->setInstanceData($data[MonitorResource::IDENTIFIER], $data[MonitorResource::ENVIRONMENT], $data['data']);
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error('Could not process Item in Monitor Queue {data}', ['data' => json_encode($data), SendToMonitorFlag::SEND_TO_MONITOR_KEY->value => false]);
      $this->logManager->alertByMail([$e->getMessage()]);
    }
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('monitor.storage'),
      $container->get('monitor.log_manager')
    );
  }
}
