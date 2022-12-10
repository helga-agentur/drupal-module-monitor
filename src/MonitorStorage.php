<?php

namespace Drupal\monitor;

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\SharedTempStoreFactory;

/**
 * Class MonitorStorage.
 *
 * This class is used to save all monitor data.
 */
class MonitorStorage {

  const TEMP_STORE_NAME = 'monitor';

  protected  $tempDataStore;

  /**
   * @param PrivateTempStoreFactory $sharedTempStore
   */
  public function __construct(SharedTempStoreFactory $sharedTempStore) {
    $this->tempDataStore = $sharedTempStore->get(self::TEMP_STORE_NAME);
  }

  /**
   * @param string $identifier
   * @param array $data
   * @return void
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function setInstanceData(string $identifier, array $data): void {
    $this->tempDataStore->set($identifier, $data);
    $this->addInstance($identifier);
  }

  /**
   * @param string $identifier
   * @return array
   */
  public function getInstanceData(string $identifier): array{
    return $this->tempDataStore->get($identifier);
  }

  /**
   * Adds an instance and makes sure it also prevents duplicates.
   *
   * @param string $instance
   * @return void
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function addInstance(string $instance) {
    $instances = $this->getInstances();
    $instances[] = $instance;
    $this->tempDataStore->set('identifiers', array_unique($instances));
  }

  /**
   * @return array
   */
  public function getInstances(): array {
    $instances = $this->tempDataStore->get('identifiers');
    return $instances ?? [];
  }

}
